<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//회원정보 추출
function getminfo($manager, &$datarow, &$minfo ,&$boardname, $adminname='',$board_view=null)
{
	$CI =& get_instance();
	$CI->load->helper('member');

	$manager['icon_admin_img'] = str_replace($CI->Boardmanager->board_data_src.$manager['id'].'/',"",$manager['icon_admin_img']);
	if( $adminname ) {
		if($manager['write_admin_type'] == 'IMG' ) {
			$manager['icon_admin_img']		= ($manager['write_admin_type'] == 'IMG' && $manager['icon_admin_img'] && is_file($CI->Boardmanager->board_data_dir.$manager['id'].'/'.$manager['icon_admin_img']) ) ? $CI->Boardmanager->board_data_src.$manager['id'].'/'.$manager['icon_admin_img'].'?'.time():$CI->Boardmanager->board_icon_src.'icon_admin.gif';
			$boardname = '<img src="'.$manager['icon_admin_img'].'" id="icon_admin_img" align="absmiddle" style="vertical-align:middle;" />';
		}else{
			$boardname = $manager['write_admin'];
		}
		return true;
	}

	$datarow['real_name'] = $datarow['name'];
	if( defined('__ADMIN__') && $datarow['mseq'] > 0 ) {//관리자 > 회원정보 통일@2013-07-23
		$boardname = '<span>';
			//$CI->load->model('membermodel');
			
			$CI->joinform = ($CI->joinform)?$CI->joinform:config_load('joinform');
			$newmbinfo = "";
			if( $CI->joinform['user_icon']=='Y') $newmbinfo .="A.user_icon_file,";
			if( $manager['writer_date'] =='all') {
				$newmbinfo .="A.regist_date,A.lastlogin_date,";
			}elseif($manager['writer_date'] =='regit') {
				$newmbinfo .="A.regist_date,";
			}elseif($manager['writer_date'] =='login') {
				$newmbinfo .="A.lastlogin_date,";
			}
			$minfo = $CI->membermodel->get_member_data_only_seq($datarow['mseq'],$newmbinfo); 
		if($datarow['mseq'] && $minfo) {
			if($minfo['bname']) {
				$datarow['real_name'] = $minfo['bname'];
			}elseif($minfo['user_name']){
				$datarow['real_name'] = $minfo['user_name'];
			}
			if($minfo['nickname']) $datarow['nickname'] = $minfo['nickname'];

			if(empty($minfo['mbinfo_business_seq'])) $boardname .= '<img src="/admin/skin/'.$CI->skin.'/images/common/icon/icon_personal.gif" /> ';
			if($minfo['mbinfo_business_seq']) $boardname .= '<img src="/admin/skin/'.$CI->skin.'/images/common/icon/icon_besiness.gif" /> ';
			$boardname .= $datarow['real_name'];
				if( $minfo['rute'] && $minfo['rute'] != 'none' ){//SNS회원인 경우
					$boardname .= '(<a href="/admin/member/detail?member_seq='.$minfo['member_seq'].'" target="_blank"><span style="color:#d13b00;"><img src="/admin/skin/'.$CI->skin.'/images/sns/sns_'.substr($minfo['rute'],0,1).'0.gif" align="absmiddle">'.$minfo['email'].'</span>/<span class="blue">'.$minfo['group_name'].'</span></a>)';
				}else{
					$boardname .= '(<a href="/admin/member/detail?member_seq='.$minfo['member_seq'].'" target="_blank"><span style="color:#d13b00;">'.$minfo['userid'].'</span>/<span class="blue">'.$minfo['group_name'].'</span></a>)';
				}
		}else{
			$boardname .= $datarow['name'].' (<span class="desc">비회원</span>)';
		}
		 
		if($minfo['user_icon'] && $CI->joinform['user_icon'] == 'Y' ){ 
			$boardicon		= (   $minfo['user_icon_file'] && $minfo['user_icon']==99 ) ?'/data/icon/member/'.$minfo['user_icon_file'].'?'.time():'/data/icon/member/'.memberIconConf($minfo['user_icon']).'?'.time();
			$boardiconimgtag = ' <img src="'.$boardicon.'" id="icon_membericon" align="absmiddle" style="vertical-align:middle;" />';
		}
		if($boardiconimgtag) $boardname = $boardiconimgtag.$boardname;
		if( $board_view ) {
			$dateformat = ($CI->siteType == 'mobile')?"y.m.d":"Y-m-d";
			if( $manager['writer_date'] == 'all') {
				$boardname .= "(가입:".getDateFormat($minfo['regist_date'],$dateformat)."/방문:".getDateFormat($minfo['lastlogin_date'],$dateformat).")";
			}elseif( $manager['writer_date'] == 'regit') {
				$boardname .= "(가입:".getDateFormat($minfo['regist_date'],$dateformat).")";
			}elseif( $manager['writer_date'] == 'login' ) {
				$boardname.= "(방문:".getDateFormat($minfo['lastlogin_date'],$dateformat).")";
			}
		}
		$boardname .= '</span>';
	}else{
		if($datarow['mseq']) {
			$userinfocs = ( defined('__SELLERADMIN__') ===  true ) ? '':'userinfo';
			if($data['boardid'] == 'gs_seller_qna' ) {//입점사문의게시판
				//$CI->load->model('providermodel');
				$minfo = $CI->providermodel->get_provider($datarow['mseq']);
				$minfo['member_seq']	= $minfo['provider_seq'];
				$minfo['userid']			= $minfo['provider_id'];
				$minfo['user_name']	= $minfo['provider_name'];
				$minfo['group_name'] = '입점사';
			}else{
				if($datarow['mseq'] != '-1') {
					//$CI->load->model('membermodel'); 
					$CI->joinform = ($CI->joinform)?$CI->joinform:config_load('joinform');
					$newmbinfo = "";
					if( $CI->joinform['user_icon']=='Y') $newmbinfo .="A.user_icon_file,";
					if( $manager['writer_date'] =='all') {
						$newmbinfo .="A.regist_date,A.lastlogin_date,";
					}elseif($manager['writer_date'] =='regit') {
						$newmbinfo .="A.regist_date,";
					}elseif($manager['writer_date'] =='login') {
						$newmbinfo .="A.lastlogin_date,";
					}
					$minfo = $CI->membermodel->get_member_data_only_seq($datarow['mseq'],$newmbinfo);
				}
			}

			if($datarow['mseq'] == '-1') {//root
				if($manager['write_admin_type'] == 'IMG' ) {
					$manager['icon_admin_img']		= ($manager['write_admin_type'] == 'IMG' && $manager['icon_admin_img'] && is_file($CI->Boardmanager->board_data_dir.$manager['id'].'/'.$manager['icon_admin_img']) ) ? $CI->Boardmanager->board_data_src.$manager['id'].'/'.$manager['icon_admin_img'].'?'.time():$CI->Boardmanager->board_icon_src.'icon_admin.gif';
					$boardname = '<img src="'.$manager['icon_admin_img'].'" id="icon_admin_img" align="absmiddle" style="vertical-align:middle;" />';
				}else{
					$boardname = $manager['write_admin'];
				}
			}elseif( ($datarow['mseq']) < -1 ) {//provider
				//$CI->load->model('providermodel');
				//$pinfo = $CI->providermodel->get_provider(str_replace("-","",$datarow['mseq']));
				$boardname = $pinfo['provider_name'];

				if( $manager['write_show'] == 'ID-NAME' ) {
					$boardname = ($pinfo['provider_id'] ) ? '<span class="" provider_id="'.$pinfo['provider_id'].'" provider_seq="'.$pinfo['provider_seq'].'" >'.$pinfo['provider_name'].' '.$pinfo['provider_id'].' </span>':'<span class="" provider_id="'.$pinfo['provider_id'].'" provider_seq="'.$pinfo['provider_seq'].'" >'.$pinfo['provider_name'].'</span> ';//회원정보
				}else{
					$boardname = ( $manager['write_show']=='ID' ) ? '<span class="" provider_id="'.$pinfo['provider_id'].'" provider_seq="'.$pinfo['provider_seq'].'" >'.$pinfo['provider_id'].'</span>':'<span class="" provider_id="'.$pinfo['provider_id'].'" provider_seq="'.$pinfo['provider_seq'].'" >'.$pinfo['provider_name'].'</span> ';//회원정보
				}

			}elseif(isset($minfo['member_seq'])) {
				$datarow['real_userid'] = $minfo['userid'];
				if($minfo['bname']) {
					$datarow['real_name'] = $minfo['bname'];
				}elseif($minfo['user_name']){
					$datarow['real_name'] = $minfo['user_name'];
				}
				if($minfo['nickname']) $datarow['nickname'] = $minfo['nickname'];

				if( strstr($manager['write_show'],"ID") ){
					$userid = $datarow['real_userid'];
					if( !( defined('__ADMIN__') || defined('__SELLERADMIN__') ) )
						if( strstr($userid, "@") ) {
							$useridar = explode("@",$userid);
							$userid = getstrcut($useridar[0],4,'***');
						}else{
							$userid = getstrcut($userid,4,'***');
						}

				}else{
					$userid = "";
				}

				if( strstr($manager['write_show'],"NAME") ){
					$user_name	= $datarow['real_name'];
				}else{
					$user_name	= "";
				}

				if( strstr($manager['write_show'],"NIC") ){
					$nickname	= ($datarow['nickname'])?$datarow['nickname']:$datarow['real_name'];
				}else{
					$nickname	= "";
				}

				if( $manager['show_name_type'] == 'HID' && !( defined('__ADMIN__') || defined('__SELLERADMIN__') ) ) {//작성자-이름/닉네임
					if($nickname)
						$nickname = getstrcut($nickname,1,'').'*'.getstrcut($nickname,-1,'',1);
					if($user_name)
						$user_name = getstrcut($user_name,1,'').'*'.getstrcut($user_name,-1,'',1);
				}
				if( !strstr($manager['write_show'],"NONE") ) {//등급/비회원문구추가여부
					if($manager['show_grade_type'] == 'IMG' && $minfo['icon']) {//작성자-등급
						$manager['icon_show_grade_img']		= '/data/icon/common/'.$minfo['icon'];
						$datarow['group_name'] = ', <img src="'.$manager['icon_show_grade_img'].'" id="icon_admin_img" align="absmiddle" style="vertical-align:middle;" />';
					}else{
						$datarow['group_name'] =  ", ".$minfo['group_name'];//'('..')'
					}
				}else{
					$datarow['group_name'] = '';
				}

				$writershowar = array("ID-NAME", "ID-NAME-NONE", "ID-NIC", "ID-NIC-NONE");//괄호넣기구문
				if( in_array($manager['write_show'], $writershowar) ) {
					$writershows = "(";
					$writershowe = ")";
				}

				if( defined('__ADMIN__')  || defined('__SELLERADMIN__') ) {
					$boardname = ($minfo['userid'] ) ? '<span class="'.$userinfocs.' hand" mid="'.$minfo['userid'].'" mseq="'.$minfo['member_seq'].'" >'.$user_name.' '.$nickname.' '.$userid.' </span> '.$datarow['group_name'].'':'<span class="'.$userinfocs.' hand" mid="'.$minfo['userid'].'" mseq="'.$minfo['member_seq'].'" >'.$user_name.' '.$nickname.' '.$userid.' </span> '.$datarow['group_name'].'';//회원정보
				}else{
					$boardname = ( isset($minfo['member_seq']) && $userid) ? ' '.$user_name.' '.$nickname.''.$writershows.''.$userid.' '.$datarow['group_name'].''.$writershowe.'':''.$user_name.''.$writershows.''.$nickname.' '.$datarow['group_name'].''.$writershowe.'';//회원정보  && $minfo['rute'] == 'none'
				}

				if($minfo['user_icon'] && $CI->joinform['user_icon'] == 'Y' ){ 
					$boardicon		= (   $minfo['user_icon_file'] && $minfo['user_icon']==99 ) ?'/data/icon/member/'.$minfo['user_icon_file'].'?'.time():'/data/icon/member/'.memberIconConf($minfo['user_icon']).'?'.time();
					$boardiconimgtag = ' <img src="'.$boardicon.'" id="icon_membericon" align="absmiddle" style="vertical-align:middle;" />';
				}
				if($boardiconimgtag) $boardname = $boardiconimgtag.$boardname;
				if( $board_view ) {
					$dateformat = ($CI->siteType == 'mobile')?"y.m.d":"Y-m-d";
					if( $manager['writer_date'] == 'all') {
						$boardname .= "(가입:".getDateFormat($minfo['regist_date'],$dateformat)." /방문:".getDateFormat($minfo['lastlogin_date'],$dateformat).")";
					}elseif( $manager['writer_date'] == 'regit') {
						$boardname .= "(가입:".getDateFormat($minfo['regist_date'],$dateformat).")";
					}elseif( $manager['writer_date'] == 'login' ) {
						$boardname.= "(방문:".getDateFormat($minfo['lastlogin_date'],$dateformat).")";
					}
				}
			}else{
				if( !defined('__ADMIN__') && !defined('__SELLERADMIN__') ) {
					$datarow['name']			= getstrcut($datarow['name'],1,'').'*'.getstrcut($datarow['name'],-1,'',1);
				}
				if( !strstr($manager['write_show'],"NONE") ) {//등급/비회원문구추가여부
					$boardname =$datarow['name'].' (비회원)';//회원정보
				}else{
					$boardname =$datarow['name'];//회원정보
				}
			}
		}else{
			if(  $manager['show_name_type'] == 'HID' && !( defined('__ADMIN__') || defined('__SELLERADMIN__') )  ) {
				$datarow['name']			= getstrcut($datarow['name'],1,'').'*'.getstrcut($datarow['name'],-1,'',1);
			}
			if( !strstr($manager['write_show'],"NONE") ) {//등급/비회원문구추가여부
				$boardname =$datarow['name'].' (비회원)';
			}else{
				$boardname =$datarow['name'];
			}
		}
	}
	unset($minfo);
}

function board_temp_image_upload($filename,$folder){
	$tmp = getimagesize($_FILES['Filedata']['tmp_name']);
	$_FILES['Filedata']['type'] = $tmp['mime'];
	$config['upload_path'] = $folder;
	$config['allowed_types'] = 'jpg|png|gif';
	$config['max_size']	= $this->config_system['uploadLimit'];
	$config['file_name'] = $filename;
	$this->load->library('upload', $config);
	if ( ! $this->upload->do_upload('Filedata'))
	{
		$result = array('status' => '0','error' => $this->upload->display_errors());
	}else{
		$result = array('status' => 1,'fileInfo'=>$this->upload->data());
	}
	return $result;
}

function board_temp_image_resize($source,$target,$width,$height){
	$this->load->library('image_lib');
	$config['image_library'] = 'gd2';
	$config['source_image'] = $source;
	$config['new_image'] = $target;
	$config['maintain_ratio'] = TRUE;
	$config['width'] = $width;
	$config['height'] = $height;
	$this->image_lib->initialize($config);
	if ( ! $this->image_lib->resize())
	{
		$result = array('status' => '0','error' => $this->image_lib->display_errors());
	}else{
		$result = array('status' => 1);
	}
	$this->image_lib->clear();
	return $result;
}

//상품정보출력
function getGoodsinfo($data, $goods_seq_data ,$type = 'list' )
{
	$CI =& get_instance();
	$CI->load->model('goodsmodel');
	$goods_seq_array = @explode(",",$goods_seq_data);
	$goods_seq_array= array_unique($goods_seq_array);
	$goodsview = '';
	$i=0;
	foreach($goods_seq_array as $goods_seq){
		if(!$goods_seq)continue;
		$goods = $CI->goodsmodel->get_goods($goods_seq);

		/* 회원 대체 가격 추가 leewh 2014-12-30 */
		$goods['string_price']		= get_string_price($goods);
		$goods['string_price_use']	= 0;
		if	($goods['string_price'] != '')	$goods['string_price_use']	= 1;

		/* 기준 할인가 기준으로 변경함 leewh 2014-11-21 */
		//$options = $CI->goodsmodel->get_goods_option($goods_seq);
		$options = $CI->goodsmodel->get_goods_default_option($goods_seq);

		$goods['opt_consumerprice'] = number_format($options[0]['consumer_price']);
		$goods['opt_price']					= number_format($options[0]['price']);
		if($goods['provider_seq']) {
			//$CI->load->model('providermodel');
			//$provider = $CI->providermodel->get_provider($goods['provider_seq']);
		}

		if(isset($goods['goods_seq'])) {

			$goodsimg = '';
			$images = $CI->goodsmodel->get_goods_image($goods_seq);
			if($CI->pagetype == 'goods') {//상품상세인경우 첨부파일로 출력
				if($data && !empty($data['filelist']['realthumbfile'])){
					$goodsimg = $data['filelist']['realthumbfile'];
				}elseif($data && !empty($data['filelist']['realfileurl'])){
					$goodsimg = $data['filelist']['realfileurl'];
				}elseif($data && !empty($data['filelist']['realfile'])){//다른폴더이거나 외부 이미지일때 추가 @2015-02-03
					$goodsimg = $data['filelist']['realfile'];
				}
			}else{
				if( $data['file_key_w'] && uccdomain('thumbnail', $data['file_key_w']) && $type == 'list') {
					$goodsimg = uccdomain('thumbnail', $data['file_key_w']);
				}else{
					if($data && !empty($data['filelist']['realthumbfile'])){
						$goodsimg = $data['filelist']['realthumbfile'];
					}elseif($data && !empty($data['filelist']['realfileurl'])){
						$goodsimg = $data['filelist']['realfileurl'];
					}elseif($data && !empty($data['filelist']['realfile'])){//다른폴더이거나 외부 이미지일때 추가 @2015-02-03
						$goodsimg = $data['filelist']['realfile'];
					}else{
						$goodsimg = ($images[1]['thumbCart']['image'])?$images[1]['thumbCart']['image']:$images[1]['list1']['image'];
					}
				}
			}

			//$providername = ($provider['provider_name'])?'['.$provider['provider_name'].']':'';

			if($type == 'write') {//수정
				$goodsview[$i]['goods_seq']		= $goods['goods_seq'];
				$goodsview[$i]['goods_name']	= $goods['goods_name'];
				$goodsview[$i]['summary']			= $goods['summary'];
				$goodsview[$i]['consumerprice'] = ($options[0]['consumer_price']);
				$goodsview[$i]['price']					= ($options[0]['price']);
				$goodsview[$i]['image']				= ($images[1]['thumbCart']['image'])?$images[1]['thumbCart']['image']:$images[1]['list1']['image'];

				//$goodsview[$i]['provider_seq']	= $goods['provider_seq'];
				//$goodsview[$i]['provider_name']	= $provider['provider_name'];

				if( strstr($CI->manager['list_show'],'[contents]') ) {
					$contents_tmp =  str_replace('&nbsp;', ' ',  str_ireplace('</p>',' ',$data['contents']));
					$goodslistcontents		= getstrcut(strip_tags($contents_tmp), $CI->manager['contcut']);
				}
				$goodsview[$i]['goodsimg']  = $goodsimg;
				$goodsview[$i]['goodslistcontents'] = $goodslistcontents;
			}elseif($type == 'view') {//상세
				$goodslistimg = ($goodsimg) ? '<div class="pic"><span class="hand highlight-link-text boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"><img  src="'.$goodsimg.'" width="80"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></div>':'';

				if ($goods['string_price_use']) {
					$goodslistprice = '<span class="name">'.$goods['string_price'].'</span>';
				} else {
					$goodslistprice  = ((int)$goods['opt_consumerprice']>(int)$goods['opt_price'])?'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #7f7777; font-weight: normal; text-decoration: line-through;">'.$goods['opt_consumerprice'].'원</span> → <span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>':'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>';
				}

				if( defined('__ADMIN__')  || defined('__SELLERADMIN__') ) {
					if($goodsimg){
						$widthc =  '80%';
					}else{
						$widthc =  '100%';
					}
					if( !$_GET['seq'] ) $goodslistprice = '';
				}else{
					if($goodsimg){
						$widthc =  '50%';
					}else{
						$widthc =  '100%';
					}
				}

				$goodsview .= '
				<div id="goodsview" class="goodsviewbox" >
					'.$goodslistimg.'
					<div class="info" style="width:'.$widthc.';">
						<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank">'.$goods['goods_name'].' '.$providername.'</a></div>
						'.$goodslistprice.'
					</div>
						<div class="cboth"></div>
				</div>
					<div class="cboth"></div>
				';
			}else{
				if( strstr($CI->manager['list_show'],'[contents]') ) {
					$contents_tmp =  str_replace('&nbsp;', ' ',  str_ireplace('</p>',' ',$data['contents']));
					$goodslistcontents		= '<div class="board_cont cell"><span class="hand highlight-link-text1  boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"> '.getstrcut(strip_tags($contents_tmp), $CI->manager['contcut']).'</span></div>';
				}

				if( defined('__ADMIN__') ) {
					$goods_name				= $goods['goods_name'].' <br/>'.$providername;
				}else{
					$goods_name				= $goods['goods_name'];
				}
				if($CI->pagetype == 'goods') {//상품상세인경우 첨부파일로 대체
					$goodslistimg = ($goodsimg) ? '<div class="pic"><span class="hand highlight-link-text boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"><a><img  src="'.$goodsimg.'" width="60"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></span></div>':'';
					$goodslistname = '';
				}else{
					$goodslistimg = ($goodsimg) ? '<div class="pic"><span class="hand highlight-link-text boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"><a><img  src="'.$goodsimg.'" width="60"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></span></div>':'';
					$goodslistname = '<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank">'.$goods_name.'</a></div>';

					if ($goods['string_price_use']) {
						$goodslistprice = '<span class="name">'.$goods['string_price'].'</span>';
					} else {
						$goodslistprice  = ((int)$goods['opt_consumerprice']>(int)$goods['opt_price'])?'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #7f7777; font-weight: normal; text-decoration: line-through;">'.$goods['opt_consumerprice'].'원</span> → <span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>':'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>';
					}
				}
				if( defined('__ADMIN__') || defined('__SELLERADMIN__')) {
					if($goodslistimg){
						$widthc =  '80%';
					}else{
						$widthc =  '100%';
					}
					if( !$_GET['seq'] ) $goodslistprice = '';
				}else{
					if($goodslistimg){
						$widthc =  '50%';
					}else{
						$widthc =  '100%';
					}
				}
				$goodsview .= '
				<div id="goodsview" class="goodsviewbox">
					'.$goodslistimg.'
					<div class="info"  style="width:'.$widthc.';">
						'.$goodslistname.'
						'.$goodslistprice.'
						<div class="board_sbj"> '.$data['iconmobile'].' '.$data['iconaward'].' '.$data['subject'].' '.$data['iconvideo'].' '.$data['iconimage'].' '.$data['iconfile'].' '.$data['iconnew'].' '.$data['iconhot'].' '.$data['iconhidden'].'</div>
						'.$goodslistcontents.'
					<div class="cboth "></div>
				</div>
					<div class="cboth"></div>
				';
			}
		}else{
			if($type == 'view'){
				$goodsview .= '상품정보가 없습니다.';
			}elseif($type == 'write') {//등록/수정
			}else{
				$goodslistimg = ($goodsimg) ? '<div class="pic"><span class="hand highlight-link-text boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"><a><img  src="'.$goodsimg.'" width="60"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></span></div>':'';

				if( strstr($CI->manager['list_show'],'[contents]') ) {
					$contents_tmp = str_replace('&nbsp;', ' ',  str_ireplace('</p>',' ',$data['contents']));
					$goodslistcontents		= '<div class="board_cont cell"  style="width:100%" ><span class="hand highlight-link-text boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"> '.getstrcut(strip_tags($contents_tmp), $CI->manager['contcut']).'</span></div>';
				}

				$goods_name				= $goods['goods_name'];

				$goodsview .= '
				<div id="goodsview" class="goodsviewbox">
					'.$goodslistimg.'
					<div class="info"  style="width:70%;">
						<div class="board_sbj"> '.$data['iconmobile'].' '.$data['iconaward'].' '.$data['subject'].' '.$data['iconvideo'].' '.$data['iconimage'].' '.$data['iconfile'].' '.$data['iconnew'].' '.$data['iconhot'].' '.$data['iconhidden'].'</div>
						'.$goodslistcontents.'
					<div class="cboth "></div>
				</div>
					<div class="cboth"></div>
				';
			}
		}
		$i++;
		if($type == 'list') {//
			if($i == 1) return $goodsview;
		}
	}
	return $goodsview;
}


//대량구매상품정보출력
function getBulkorderGoodsinfo($data, $goods_seq_data ,$type = 'list' )
{
	$CI =& get_instance();
	$CI->load->model('goodsmodel');
	$goods_seq_array = @explode(",",$goods_seq_data);
	$goods_cont_array = @explode("^|^",$data['goods_cont']);
	$goods_seq_array= array_unique($goods_seq_array);
	$goodsview = '';
	$i=0;
	foreach($goods_seq_array as $goods_seq){
		$goods = $CI->goodsmodel->get_goods($goods_seq);
		$options = $CI->goodsmodel->get_goods_option($goods_seq);
		$goods['opt_consumerprice'] = number_format($options[0]['consumer_price']);
		$goods['opt_price']					= number_format($options[0]['price']);

		if(isset($goods['goods_seq'])) {
			$images = $CI->goodsmodel->get_goods_image($goods_seq);
			if($CI->pagetype == 'goods') {//상품상세인경우 첨부파일로 출력
				if( $data && !empty($data['filelist']['realfileurl']) ) {
					$goodsimg = $data['filelist']['realfileurl'];
				}
			}else{
				if($data && !empty($data['filelist']['realfileurl'])){
					$goodsimg = $data['filelist']['realfileurl'];
				}else{
					$goodsimg = $images[1]['list1']['image'];
				}
			}
			if($type == 'write') {//수정

				$goodsview[$i]['goods_seq']		= $goods['goods_seq'];
				$goodsview[$i]['goods_cont']		= $goods_cont_array[$i];
				$goodsview[$i]['goods_name']	= $goods['goods_name'];
				$goodsview[$i]['consumerprice'] = ($options[0]['consumer_price']);
				$goodsview[$i]['price']					= ($options[0]['price']);
				$goodsview[$i]['image']				= $images[1]['list1']['image'];

			}elseif($type == 'view') {//상세

				$goodslistimg = ($goodsimg) ? '<div class="pic"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><img  src="'.$goodsimg.'" width="80"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></div>':'';

				$goodslistprice  = ((int)$goods['opt_consumerprice']>(int)$goods['opt_price'])?'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #7f7777; font-weight: normal; text-decoration: line-through;">'.$goods['opt_consumerprice'].'원</span> → <span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>':'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>';

				if( defined('__ADMIN__') || defined('__SELLERADMIN__')) {
					$widthc =  '70%';
				}else{
					$widthc =  '50%';
				}

				$goodsview .= '
				<div id="goodsview" class="goodsviewbox" >
					'.$goodslistimg.'
					<div class="info" style="width:'.$widthc.';">
						<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank">'.$goods['goods_name'].'</a></div>
						'.$goodslistprice.'
						<div class="cont"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank">'.$goods['summary'].'</a></div>
						<div class="cont">'.$goods_cont_array[$i].'</div>
					</div>
						<div class="cboth"></div>
				</div>
					<div class="cboth"></div>
				';
			}else{
				if( strstr($CI->manager['list_show'],'[contents]') ) {
					$contents_tmp =  str_replace('&nbsp;', ' ',  str_ireplace('</p>',' ',$data['contents']));
					$goodslistcontents		= '<div class="cont cell"><span class="hand highlight-link boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"> '.getstrcut(strip_tags($contents_tmp), $CI->manager['contcut']).'</span></div>';
				}

				$goods_name				= $goods['goods_name'];
				if($CI->pagetype == 'goods') {//상품상세인경우 첨부파일로 대체
					$goodslistimg = ($goodsimg) ? '<div class="pic"><span class="hand highlight-link boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"><img  src="'.$goodsimg.'" width="80"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></span></div>':'';
					$goodslistname = '';
				}else{
					$goodslistimg = ($goodsimg) ? '<div class="pic"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><img  src="'.$goodsimg.'" width="80"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></div>':'';
					$goodslistname = '<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank">'.$goods_name.'</a></div>';

					$goodslistprice  = ((int)$goods['opt_consumerprice']>(int)$goods['opt_price'])?'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #7f7777; font-weight: normal; text-decoration: line-through;">'.$goods['opt_consumerprice'].'원</span> → <span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>':'<div class="name"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><span style="color: #cc0000;">'.$goods['opt_price'].'원</span> </a></div>';
				}
				if( defined('__ADMIN__') || defined('__SELLERADMIN__')) {
					$widthc =  '70%';
				}else{
					$widthc =  '50%';
				}
				$goodsview .= '
				<div id="goodsview" class="goodsviewbox">
					'.$goodslistimg.'
					<div class="info"  style="width:'.$widthc.';">
						'.$goodslistname.'
						'.$goodslistprice.'
						<div class="cont"> '.$data['iconmobile'].$data['subject'].' '.$data['iconimage'].' '.$data['iconfile'].' '.$data['iconnew'].' '.$data['iconhot'].' '.$data['iconhidden'].'</div>
						'.$goodslistcontents.'
					<div class="cboth "></div>
				</div>
					<div class="cboth"></div>
				';
			}
		}else{
			if($type == 'view'){
				$goodsview .= '상품정보가 없습니다.';
			}elseif($type == 'write') {//등록/수정
			}else{
				$goodslistimg = ($goodsimg) ? '<div class="pic"><a href="'.$CI->boardurl->goodsview.$goods['goods_seq'].'" target="_blank"><img  src="'.$goodsimg.'" width="80"   alt="" class="hand small_goods_image pic"  onerror="this.src=\'/data/icon/error/noimage_list.gif\'" /></a></div>':'';

				if( strstr($CI->manager['list_show'],'[contents]') ) {
					$contents_tmp = str_replace('&nbsp;', ' ',  str_ireplace('</p>',' ',$data['contents']));
					$goodslistcontents		= '<div class="cont cell"  style="width:100%" ><span class="hand highlight-link boad_view_btn'.$CI->manager['isperm_read'].'" viewlink="'.$CI->boardurl->view.$data['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$data['seq'].'"  board_id="'.BOARDID.'"> '.getstrcut(strip_tags($contents_tmp), $CI->manager['contcut']).'</span></div>';
				}

				$goods_name				= $goods['goods_name'];

				$goodsview .= '
				<div id="goodsview" class="goodsviewbox">
					'.$goodslistimg.'
					<div class="info"  style="width:70%;">
						<div class="cont"> '.$data['iconmobile'].$data['subject'].' '.$data['iconimage'].' '.$data['iconfile'].' '.$data['iconnew'].' '.$data['iconhot'].' '.$data['iconhidden'].'</div>
						'.$goodslistcontents.'
					<div class="cboth "></div>
				</div>
					<div class="cboth"></div>
				';
			}
		}
		$i++;
		if($type == 'list') {//
			if($i == 1) return $goodsview;
		}
	}
	return $goodsview;
}

//관리자>게시글관리>적립금노출
function getBoardEmoneyAutotxt($data, &$reviewless)
{
	$CI =& get_instance();
	$CI->load->model('emoneymodel');
	$emautoemoneylay ='';
	if( $data['mseq'] < 0 ) return false;

	$emautoemoneysc = $CI->db->query("select  emoney, type, emoney_seq, ordno   from fm_emoney where emoney_use !='less' and  (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$data['mseq']."' and ( (ordno  = '".$data['order_seq']."' and goods_review = '".$data['goods_seq']."' and (ordno != '' or ordno != 0 ) and goods_review_parent='".$data['seq']."' ) or ( goods_review_parent='".$data['seq']."' ) ) ");
	$emautoemoneyar = $emautoemoneysc->result_array();

	foreach($emautoemoneyar as $emautoemoneyck=>$emautoemoney) {
		if($emautoemoney){
			if( $emautoemoney['type'] == 'goods_review_auto_photo' ){
				$autophoto = "포토";
			}elseif($emautoemoney['type'] == 'goods_review_auto_video' ){
				$autophoto = "동영상";
			}elseif($emautopoint['type'] == 'goods_review_date' ){
				$autophoto = "특정기간";
			}else{
				$autophoto = "일반";
			}
			$reviewless['emoney'] += $emautoemoney['emoney'];
			$emautoemoneylay1 .= ($emautoemoney['ordno'])?'<span class="hand blue orderview" order_seq="'.$emautoemoney['ordno'].'" >'.$emautoemoney['ordno'].'</span><br/>':'';
			$emautoemoneylay1 .= $autophoto.' '.number_format($emautoemoney['emoney']).'원<br/>';
		}
	}//endfor

	$emautopointsc = $CI->db->query("select  point, type, point_seq, ordno from fm_point where point_use !='less' and  (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$data['mseq']."' and ( (ordno  = '".$data['order_seq']."' and goods_review = '".$data['goods_seq']."' and (ordno != '' or ordno != 0 ) and  goods_review_parent='".$data['seq']."' ) or ( goods_review_parent='".$data['seq']."' ) ) ");
	$emautopointar = $emautopointsc->result_array();

	foreach($emautopointar as $emautopointck=>$emautopoint) {
		if($emautopoint){
			if( $emautopoint['type'] == 'goods_review_auto_photo' ){
				$autophoto = "포토";
			}elseif($emautopoint['type'] == 'goods_review_auto_video' ){
				$autophoto = "동영상";
			}elseif($emautopoint['type'] == 'goods_review_date' ){
				$autophoto = "특정기간";
			}else{
				$autophoto = "일반";
			}
			$reviewless['point'] += $emautopoint['point'];
			$emautoemoneylay2 .= ($emautopoint['ordno'])?'<span class="hand blue orderview" order_seq="'.$emautopoint['ordno'].'" >'.$emautopoint['ordno'].'</span><br/>':'';
			$emautoemoneylay2 .= $autophoto.' '.number_format($emautopoint['point']).'P<br/>';
		}
	}//endfor

	$emautoemoneylay = ($emautoemoneylay1 && $emautoemoneylay2)? $emautoemoneylay1."<br/>".$emautoemoneylay2:$emautoemoneylay1." ".$emautoemoneylay2;
	return $emautoemoneylay;
}

//권한체크 isperm
function get_auth($manager, $data , $mode, & $isperm)
{
	$CI =& get_instance();
	if( defined('__ISUSER__') === true ) {
		$CI->load->model('membermodel');
		$CI->minfo = $CI->membermodel->get_member_data($CI->userInfo['member_seq']);
	}
	$isperm['isperm_'.$mode] = true;//
	$isperm['fileperm_'.$mode] = '';
	$manager['auth_'.$mode.'_use'] = (!empty($manager['auth_'.$mode.'_use']))?$manager['auth_'.$mode.'_use']:'Y';

	if( $manager['auth_'.$mode.'_use'] == 'N') {//미사용
		//if(!defined('__ISADMIN__')) {
			$isperm['isperm_'.$mode] = false;//접근불허
			$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];//권한없음페이지이동
		//}

	}elseif( $manager['auth_'.$mode.'_use'] == 'Y' ) {//사용인경우

		if ( strstr($manager['auth_'.$mode],'[member]') || strstr($manager['auth_'.$mode],'[memberall]') ) {//회원제한인경우 or 회원전체인경우

			$isperm['isauth_mb'.$mode] = true;//회원전용

			if( !isset($CI->userInfo['member_seq']) ) {
				//if(!defined('__ISADMIN__')) {
					$isperm['isperm_'.$mode] = false;//접근불허
					$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
				//}
				//$isperm['fileperm_'.$mode] = '../member/login';//로그인 페이지이동
			}else{
				if( !strstr($manager['auth_'.$mode],'[group:'.$CI->minfo['group_seq'].']') && strstr($manager['auth_'.$mode],'[member]')) {//회원인데 접근권한이 없는경우
					//if(!defined('__ISADMIN__')) {
						$isperm['isperm_'.$mode] = false;//접근불허
						$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
					//}
				}
			}

		}elseif(strstr($manager['auth_'.$mode],'[admin]') ) {//관리자만->관리자페이지에서이용가능
			$isperm['isauth_admin'.$mode] = true;//관리자전용

			if(isset($data['seq']) && !strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess) ) {
				//if(!defined('__ISADMIN__')){
					$isperm['isperm_'.$mode] = false;//접근불허
					$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
				//}
			}else{
				//if(!defined('__ISADMIN__')) {
					$isperm['isperm_'.$mode] = false;//접근불허
					$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
				//}
			}
		}else{
			if($mode == 'write'){//비회원 > 등록/수정/삭제권한체크
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwwrite_name = 'board_pwwrite_'.$manager['id'];
				$boardpwwritess = $CI->session->userdata($ss_pwwrite_name);
				if(isset($data['seq']) && !strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess) ) {
					//if(!defined('__ISADMIN__')) {
						$isperm['isperm_'.$mode] = false;//접근불허
						$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
					//}
				}
			}else{
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwhidden_name = 'board_pwhidden_'.$manager['id'];
				$boardpwhiddenss = $CI->session->userdata($ss_pwhidden_name);
				if( $data['hidden'] == 1 &&  empty($data['mseq']) && !strstr($boardpwhiddenss,'['.$data['seq'].']')  ) {
					//if(!defined('__ISADMIN__')){
						$isperm['isperm_'.$mode] = false;//접근불허
						$isperm['fileperm_'.$mode] = '../board/permcheck?id='.$manager['id'];
					//}
				}
			}
		}
	}

	// 비번입력후 브라우저를 닫기전까지는 등록/삭제가능함
	$ss_pwwrite_name = 'board_pwwrite_'.$manager['id'];
	$boardpwwritess = $CI->session->userdata($ss_pwwrite_name);


	//등록자인경우와 비밀번호 보기/수정/삭제 권한검수 된경우  defined('__ISADMIN__') === true ||
	if((!empty($data['mseq']) && $data['mseq'] == $CI->userInfo['member_seq'] && defined('__ISUSER__') === true) || (!empty($data['seq']) && strstr($boardpwwritess,'['.$data['seq'].']') && !empty($boardpwwritess))) {
		$isperm['isperm_moddel'] = true;//접근허
		if (!empty($data['mseq']) && $data['mseq'] == $CI->userInfo['member_seq'] && defined('__ISUSER__') === true) {
			$isperm['isperm_moddel_mb'] = true;//접근허
		}
	}else{
		$isperm['isperm_moddel'] = false;//접근불허
	}
}

//권한체크 isperm
function get_auth_btn(&$manager, $data)
{ 
	$CI =& get_instance();
	if($data['hidden'] == 1 && $data['notice'] != '1' ) {//공지글이 아니면서 비밀글 && !defined('__ISADMIN__')
		$parentsql['whereis']	= ' and seq= "'.$data['parent'].'" ';
		$parentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent';
		
		if ( $CI->widgetboardid ) {
			$parentdata = $CI->wigetBoardmodel->get_data($parentsql);//게시물정보
		}else{
			$parentdata = $CI->Boardmodel->get_data($parentsql);//게시물정보
		} 
		if( $CI->userInfo['member_seq'] ) {
			$whereis	= ' and left(gid,8) =  "'.substr($data['gid'],0,8).'"  and mseq = "'.$CI->userInfo['member_seq'].'" ';
			if( $manager['id'] == 'goods_qna' || $manager['id'] == 'goods_review' ||  $manager['id'] == 'bulkorder') {
				$prenextsql['whereis']	= $whereis;
			}else{
				$prenextsql['whereis']	= ' and boardid = "'.$manager['id'].'" '.$whereis;
			}
			$prenextsql['orderby']	= ' gid desc';
			if ( $CI->widgetboardid ) { 
				$prenextdata = $CI->wigetBoardmodel->get_data_numrow($prenextsql);
			}else{
				$prenextdata = $CI->Boardmodel->get_data_numrow($prenextsql);
			}
		}

		if( !$prenextdata ) { 
			if( $data['mseq'] > 0  || ( $data['parent'] && $parentdata['mseq'] > 0 ) ) {//회원이 쓴글인경우
				if( ( ($data['mseq'] != $CI->userInfo['member_seq'] && $parentdata['mseq'] != $CI->userInfo['member_seq']) && defined('__ISUSER__'))  || ( !defined('__ISUSER__') ) ) {//작성자가 아니거나 비회원인 경우
					$manager['isperm_read'] = '_mbno';
					$manager['fileperm_read']= '';
				}
			}else{
				// 비번입력후 브라우저를 닫기전까지는 접근가능함
				$ss_pwhidden_name = 'board_pwhidden_'.$manager['id'];
				$boardpwhiddenss = $CI->session->userdata($ss_pwhidden_name);
				if( !strstr($boardpwhiddenss,'['.$data['seq'].']') && isset($boardpwhiddenss)) {//($data['mseq']> 0 ) &&
					$manager['isperm_read'] = '_no';
					$manager['fileperm_read']= $CI->boardurl->pw.urlencode($CI->boardurl->view.$data['seq']);
				}
			}
		}
	}else{
		if(strstr($manager['auth_read'],'[admin]') ) {//관리자만보기인경우
			$parentsql['whereis']	= ' and seq= "'.$data['parent'].'" ';
			$parentsql['select']		= '  seq, gid, comment, upload, depth, pw, mseq, mid, parent'; 
			if ( $CI->widgetboardid ) {
				$parentdata = $CI->wigetBoardmodel->get_data($parentsql);//게시물정보
			}else{
				$parentdata = $CI->Boardmodel->get_data($parentsql);//게시물정보
			} 

			if( $data['mseq'] > 0  || ($data['parent'] && $parentdata['mseq'] > 0 ) ) {//회원이 쓴글인경우
				if( ( ($data['mseq'] != $CI->userInfo['member_seq'] && $parentdata['mseq'] != $CI->userInfo['member_seq']) && defined('__ISUSER__'))  || ( !defined('__ISUSER__') ) ) {//작성자가 아니거나 비회원인 경우
					$manager['isperm_read'] = '_mbno';
					$manager['fileperm_read']= '';
				}
			}
		}elseif ( strstr($manager['auth_read'],'[member]') || strstr($manager['auth_read'],'[memberall]') ) {//회원제한인경우 or 회원전체인경우
			//if( !defined('__ISADMIN__') ) {
				if( !defined('__ISUSER__') ) {//비회원인 경우
					$manager['isperm_read'] = '_mbno';
					$manager['fileperm_read']= '';
				}elseif($isperm['isperm_read'] === false){//등급이 아닌경우
					$manager['isperm_read'] = '_authno';
					$manager['fileperm_read']= '';
				}
			//}
		}
	}
}

//첨부파일 여부
function getBoardFileck($upload, $contents) {
	$CI =& get_instance();
	if( $CI->wigetBoardmodel ){
		$Boardmodel = $CI->wigetBoardmodel;
	}else{
		$Boardmodel = $CI->Boardmodel;
	}
	$CI->load->helper("file");
	if($upload) {
		$uploadar = @explode("|",$upload);
		foreach($uploadar as $filenamear){
			$filelistar = @explode("^^",$filenamear);
			@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
				$realfile = iconv('utf-8','cp949',$realfile);
			}
			if(empty($typefile)) {
			$imagescales = @getImageSize($Boardmodel->upload_path.$realfile);
				$is_image			= ($filetypetmp  && preg_match("/image/",$imagescales['mime']) )?1:0;
			}else{  
				$is_image			= ( preg_match("/image/",$typefile) )?1:0;
			}
			//$imagescales = @getImageSize($Boardmodel->upload_path.$realfile);preg_match("/image/",$imagescales['mime'])
			if(is_file($Boardmodel->upload_path.$realfile) && !$is_image) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Validate the image
 *
 * @return	bool
 */
function boardisimage($upload, $contents)
{
	$CI =& get_instance();
	if( $CI->wigetBoardmodel ){
		$Boardmodel = $CI->wigetBoardmodel;
	}else{
		$Boardmodel = $CI->Boardmodel;
	}
	$CI->load->helper("file");
	if($upload) {
		$uploadar = @explode("|",$upload);
		foreach($uploadar as $filenamear){
			$filelistar = @explode("^^",$filenamear);
			@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
				$realfile = iconv('utf-8','cp949',$realfile);
			}
			if(empty($typefile)) {
			$imagescales = @getImageSize($Boardmodel->upload_path.$realfile);
				$is_image			= ($filetypetmp && preg_match("/image/",$imagescales['mime']) )?1:0;
			}else{  
				$is_image			= ( preg_match("/image/",$typefile) )?1:0;
			}
			//$imagescales = @getImageSize($Boardmodel->upload_path.$realfile);preg_match("/image/",$imagescales['mime'])
			if(is_file($Boardmodel->upload_path.$realfile) && $is_image ) {
				return true;
			}
		}
	}
	@preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$contents,$list_image);
	if($list_image[1]) {
		$list_imagear = @explode(" ",$list_image[1]);
		$filenamear = $list_imagear[0];

		$filelistar		= @explode("/",$filenamear);
		$thumbimg	= @end($filelistar);
		$realfile		= (strstr($thumbimg,'temp_') && is_file($Boardmodel->upload_path.str_replace('temp_','',$thumbimg)))?str_replace('temp_','',$thumbimg):$thumbimg;

		if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
			$realfile = iconv('utf-8','cp949',$realfile);
		}
		if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$filenamear)) {//데이타이전->한글파일명처리
			$filenamear = iconv('utf-8','cp949',$filenamear);
		}

		$writefilesize = get_file_info($Boardmodel->upload_path.$realfile, 'size');
		$orignalfile	= $filenamear;
		$sizefile		= $writefilesize['size'];
		$typefile		= @end(explode('.', $realfile));//확장자추출
		if( $writefilesize ){
			return true;
		}else{
			$writefilesize = get_file_info(ROOTPATH.str_replace("../","/",$orignalfile), 'size');
			$orignalfile	= str_replace("../","/",$filenamear);
			$sizefile		= $writefilesize['size'];
			$typefile		= @end(explode('.', $realfile));//확장자추출
			if( $writefilesize ){//다른폴더인경우(이전등의사유로)
				return true;
			}
		}
	}
	return false;
}

//상품후기 평점
function getGoodsScore($score,  $manager, $type='view', $index=0){
	$CI =& get_instance();
	$CI->icon_review_img			= ($manager['icon_review_img'] && @is_file($CI->Boardmodel->upload_path.$manager['icon_review_img']) ) ? $CI->Boardmanager->board_data_src.$manager['id'].'/'.$manager['icon_review_img'].'?'.time():$CI->Boardmanager->review_icon_src;

	$scoreview = '';
	$sc = 6;
	if($type == 'write') {
		if($manager['goods_review_type'] == 'IMAGE'){
			for($i=5;$i>0;$i--){
				$sc--;
				$ck = ( ( $score>=0 && $score == $sc) || ($score<0 && $sc==5) ) ? ' checked ':' ';
				$scoreview .= ' <input type="radio" name="reviewcategory['.$index.'][]" id="score'.$index.$sc.'" value="'.$sc.'" '.$ck.'/>';
				$scoreviewimg = '';
					for($j=0;$j<$sc;$j++) {
						$scoreviewimg .= '<img  src="'.$CI->icon_review_img.'" title="review" valign="absmiddle" >';
					}
				$scoreview .= '<label for="score'.$index.$sc.'" >'.$scoreviewimg.'</label>';
			}
		}else{
			for($i=5;$i>0;$i--){
				$sc--;
				$ck = ( ( $score>=0 && $score == $sc) || ($score<0 && $sc==5) )  ? ' checked ':' ';
				$scoreview .= ' <input type="radio" name="reviewcategory['.$index.'][]" id="score'.$index.$sc.'" value="'.$sc.'" '.$ck.'/>';
				$scoreview .= '<label for="score'.$index.$sc.'" >'.$sc.'</label>';
			}
		}
	}else if($type == 'm_v2_write') {
		$scoreview .= '<div>';
			$scoreview .= '<table class="scoreboxlay" border="0" cellspacing="0" cellspacing="0" width="100%">';
			$scoreview .= '<tr>';
			$scoreview .= '<td width="30"><div class="scorebox review_scoreM">-</div></td>';
			$scoreview .= '<td width="30%"><div class="review_showScore"></div></td>';
			$scoreview .= '<td width="30"><div class="scorebox review_scoreP">+</div></td>';
			$scoreview .= '<td>';
			$scoreview .= '<div class="review_nowScore">';
			$scoreview .= '<input type="text" class="onlynumber score_avg review_score review_score_number" name="reviewcategory['.$index.'][]"  size="3" maxlength="2" value="'.$score.'" />점';
			$scoreview .= '</div>';
			$scoreview .= '</td>';
			$scoreview .= '</tr>';
			$scoreview .= '</table>';
			$scoreview .= '</div>';
	}else if($type == 'm_write') {
		$scoreview .= '<select name="reviewcategory[]" id="score'.$index.$sc.'" />';
		for($i=5;$i>=1;$i--){
			$sc--;
			$ck = ( ( $score>=0 && $score == $sc) || ($score<0 && $sc==5) ) ? ' selected ':' ';
			$scoreviewimg = '';
			for($j=0;$j<$sc;$j++) {	$scoreviewimg .= '★'; }
			for($k=0;$k<(5-$sc);$k++) {	$scoreviewimg .= '☆'; }
			$scoreview .= '<option value="'.$i.'">'.$scoreviewimg.'('.$i.'점)</option>';
		}
		$scoreview .= '</select>';
	}else{//view
		if($manager['goods_review_type'] == 'IMAGE' ) {
			for($i=0;$i<$score;$i++){
				$scoreview .= '<img src="'.$CI->icon_review_img.'" title="'.$score.'" valign="absmiddle" >';
			}
		}else{
			$scoreview = $score;
		}
	}
	return $scoreview;
}


//상품후기 사이즈/색상
function getsizecolor($datatype, $form='size', $type='view'){
	$CI =& get_instance();
	$CI->load->model('goodsreview');
	if($form == 'color') {
		$typear = $CI->goodsreview->colortype;
	}else{
		$typear = $CI->goodsreview->sizetype;
	}

	$dataview = '';
	if($type == 'write'){//등록수정 radio
		for($i=1;$i<count($typear);$i++){
			$ck = (!empty($datatype) && $datatype == $i)  ? ' checked ':' ';
			$dataview .= ' <label ><input type="radio" name="'.$form.'type" id="score'.$i.'" value="'.$i.'" '.$ck.'/>'.$typear[$i].'</label>';
		}
	}else{
		$dataview = $typear[$datatype];
	}

	return ($dataview)?$dataview:$typear[0];
}
function getsizecolormax($array){
	foreach ($array as $key => $val) {
	if ($val == max($array)) return $key;
	}
}

//상품후기 >상품상세 상단 통계추가  total, percent
function getsizecolorlistop($goods_seq) {
	if(empty($goods_seq)) return false;
	$CI =& get_instance();
	$colortypear = $CI->Boardmodel->colortype;
	$totalstatistic['colorlisttop'] = array();
	$colortotal = $sizetotal = $maxcolor = $maxsize = 0;

	for($i=0;$i<count($colortypear);$i++) {
		if($i>0){
			$sql['whereis']	= ' and goods_seq=\''.$_GET['goods_seq'].'\' and colortype=\''.$i.'\' ';
		}else{
			$sql['whereis']	= ' and goods_seq=\''.$_GET['goods_seq'].'\' and (colortype<=0) ';
		}

		$sql['select']		= ' seq ';
		$totalstatistic['colorlisttop'][$i]['total'] = $CI->Boardmodel->get_data_numrow($sql);
		$colortotal  += (int) $totalstatistic['colorlisttop'][$i]['total'];
	}

	$totalstatistic['colorlisttop'][getsizecolormax($totalstatistic['colorlisttop'])]['max'] = 1;

	for($i=0;$i<count($colortypear);$i++) {
		$totalstatistic['colorlisttop'][$i]['percent'] = (int) (($totalstatistic['colorlisttop'][$i]['total']/$colortotal) * 100);
	}

	$sizetypear = $CI->Boardmodel->sizetype;
	$totalstatistic['sizelisttop'] = array();

	for($i=0;$i<count($sizetypear);$i++) {
		if($i>0){
			$sql['whereis']	= ' and goods_seq=\''.$_GET['goods_seq'].'\' and sizetype=\''.$i.'\' ';
		}else{
			$sql['whereis']	= ' and goods_seq=\''.$_GET['goods_seq'].'\' and (sizetype<=0) ';
		}
		$sql['select']		= ' seq ';
		$totalstatistic['sizelisttop'][$i]['total'] = $CI->Boardmodel->get_data_numrow($sql);
		$sizetotal  += (int) $totalstatistic['sizelisttop'][$i]['total'];
	}

	$totalstatistic['sizelisttop'][getsizecolormax($totalstatistic['sizelisttop'])]['max'] = 1;

	for($i=0;$i<count($colortypear);$i++) {
		$totalstatistic['sizelisttop'][$i]['percent'] =(int) (($totalstatistic['sizelisttop'][$i]['total']/$sizetotal) * 100);
	}
	return $totalstatistic;
}

//상품후기 수동적립금
function getBoardEmoneybtn($data, $manager, $type = 'list'){
	$CI =& get_instance();
	$CI->load->model('emoneymodel');
	if( $data['mseq'] ==  '-1' ) return false;
	getminfo($manager, $data, $minfo, $mbname);//회원정보
	if( $manager['id'] == 'goods_review' ){
		$joinsc['whereis'] = " and type = 'goods_review' and gb = 'plus' and member_seq = '".$data['mseq']."' and goods_review = '".$data['seq']."' ";
		$joinsc['select']	= ' emoney ';
		$emjoinck = $CI->emoneymodel->get_data($joinsc);//수동적립금 지급여부
		$emoney = ($emjoinck['emoney']>0)?$emjoinck['emoney']:'';

		if($type=='view') {
			$emoneyview = number_format($emoney);
		}elseif($type=='viewdelete') {
			$emoneyview = $emoney;
		}elseif($type=='use') {
			$emoneyview = ($emoney)?true:false;
		}else{//btn
			$mbtel = (isset($minfo['cellphone']))?$minfo['cellphone']:$minfo['phone'];
			if($emoney == 0 && isset($minfo['member_seq']) && isset($data['mseq'])) {
				if($data['display'] != 1 ) {
					$emoneyview = ' <span class="btn small cyanblue"><button type="button"  name="review_emoneyt_btn" mbname="'.$minfo['user_name'].'"  mbtel="'.$mbtel.'" class="review_emoneyt_btn" managername="'.$manager['name'].'"  managerid="'.$manager['id'].'" board_seq="'.$data['seq'].'" mid="'.$data['mid'].'" mseq="'.$data['mseq'].'" >지급</button></span>';
				}
			}elseif($emoney == 0 && !($data['mseq'])) {
				$emoneyview = '비회원';
			}else{
				if($data['mseq'] == '-1') {//root
					$emoneyview = '';
				}else{
					$emoneyview = ($type=='list')?number_format($emoney):'';
				}
			}
		}
	}else{
		$joinsc['whereis'] = " and type = 'board_".$manager['id']."' and gb = 'plus' and member_seq = '".$data['mseq']."' and goods_review = '".$data['seq']."' ";
		$joinsc['select']	= ' emoney ';
		$emjoinck = $CI->emoneymodel->get_data($joinsc);//수동적립금 지급여부
		$emoney = ($emjoinck['emoney']>0)?$emjoinck['emoney']:'';

		if($type=='view') {
			$emoneyview = number_format($emoney);
		}elseif($type=='viewdelete') {
			$emoneyview = $emoney;
		}elseif($type=='use') {
			$emoneyview = ($emoney)?false:true;
		}else{
			$mbtel = (isset($minfo['cellphone']))?$minfo['cellphone']:$minfo['phone'];
			if($emoney == 0 && isset($minfo['member_seq']) && isset($data['mseq'])) {
				$emoneyview = ' <span class="btn small cyanblue"><button type="button"  name="board_emoneyt_btn" mbname="'.$minfo['user_name'].'"  mbtel="'.$mbtel.'" class="board_emoneyt_btn" managername="'.$manager['name'].'"  managerid="'.$manager['id'].'" board_seq="'.$data['seq'].'" mid="'.$data['mid'].'" mseq="'.$data['mseq'].'" >지급</button></span>';
			}elseif($emoney == 0 && !($data['mseq'])) {
				$emoneyview = '비회원';
			}else{
				if($data['mseq'] == '-1') {//root
					$emoneyview = '';
				}else{
					$emoneyview = ($type=='list')?number_format($emoney):'';
				}
			}
		}
	}

	return $emoneyview;
}

//상품후기 자동적립금
function getBoardAutoEmoneybtn($data, $manager, $type = 'list'){//^^image/
	$CI =& get_instance();
	$CI->load->model('emoneymodel');

	getminfo($manager, $data, $minfo, $mbname);//회원정보
	$joinsc['whereis'] = " and (type = 'goods_review_auto' or type = 'goods_review_auto_photo' or type = 'goods_review_auto_video' or type = 'goods_review_date') and gb = 'plus' and member_seq = '".$data['mseq']."' and ordno  = '".$data['order_seq']."' and goods_review = '".$data['goods_seq']."' ";//주문번호 and 회원 and 주문상품
	$joinsc['select']	= ' emoney, type ';
	$emjoinck = $CI->emoneymodel->get_data($joinsc);//수동적립금 지급여부
	$emoney = ($emjoinck['emoney']>0)?$emjoinck['emoney']:0;

	if($type=='view') {
		$emoneyview = number_format($data['emoney']);
	}else{
		$mbtel = (isset($minfo['cellphone']))?$minfo['cellphone']:$minfo['phone'];
		if($data['emoney'] == 0 && !($data['mseq'])) {
			$emoneyview = '비회원';
		}else{
			$emoneyview = ' <span class="btn small cyanblue">'.$data['order_seq'].'</span>';
			if ($emjoinck['type'] == 'goods_review_auto') {
				$emoneyview .= '일반';
			}elseif($emjoinck['type'] == 'goods_review_auto_photo'){
				$emoneyview .= '포토';
			}elseif($emjoinck['type'] == 'goods_review_auto_video'){
				$emoneyview .= '동영상';
			}elseif($emjoinck['type'] == 'goods_review_date'){
				$emoneyview .= '특정기간';
			}
			//$emoneyview .= ($emjoinck['type'] == 'goods_review_auto_photo')?'포토':'일반';
			$emoneyview .= ($type=='list')?number_format($data['emoney']):'';
		}
	}
	return $emoneyview;
}

/*
 * 스킨타입
 * @param
*/
function BoardManagerskinlist($skin = '')
{
	$CI =& get_instance();
	//$CI->load->model('Boardmanager');
	$CI->load->helper('directory');
	$filePath = $CI->Boardmanager->board_originalskin_dir;
	$map = directory_map($filePath);
	foreach($map as $dir => $dirRow) {
		if(is_array($dirRow)) {
			foreach($dirRow as $modulePath) {
				if($modulePath == $skin && $skin) {
					if($modulePath == 'name.txt'){
						$skintypelist[$dir] = implode('',file($filePath.$dir."/".$modulePath));//skinid->skinname
					}
					return $skintypelist;
				}else{
					if($modulePath == 'name.txt'){
						$skintypelist[$dir] = implode('',file($filePath.$dir."/".$modulePath));//skinid->skinname
					}
				}
			}
		}
	}

	return $skintypelist;
}

/*
 * 게시판업로드용폴더
 * @param
*/
function boarduploaddir($manager)
{
	if(empty($manager['id']))return false;
	$CI =& get_instance();
	//$CI->load->model('Boardmanager');
	$filePath = $CI->Boardmanager->board_data_dir.$manager['id'].'/';
	if( !in_array($manager['id'], $CI->Boardmanager->renewlist) )  {//기본스킨과는 구분
		$board_dir = $CI->Boardmanager->board_skin_dir.$manager['id'].'/';
		/* 디렉토리가 없으면 생성 */
		if(!is_dir($board_dir)){
			@mkdir($board_dir);@chmod($board_dir,0777);
		}
 		getBoardSkinCopy($manager, $board_dir);//게시판 스킨생성 및 복사
	}

	if(is_dir($filePath)){
		$returnPath = $CI->Boardmanager->board_data_src.$manager['id'].'/';
		return $returnPath;
	}else{
		@mkdir($filePath);
		@chmod($filePath,0777);
		$returnPath = $CI->Boardmanager->board_data_src.$manager['id'].'/';
		return $returnPath;
	}
}



//게시판스킨복사하기..
function getBoardSkinCopy($manager, $board_dir){
	$CI =& get_instance();
	//$CI->load->model('Boardmanager');
	$CI->load->helper(array('directory','design'));
	$board_skin = $board_dir.$manager['skin'];
	/* 디렉토리가 없으면 생성 */
	if(!is_dir($board_skin)){
		$category_config = skin_configuration($CI->skin);
		if( is_dir($CI->Boardmanager->board_originalskin_dir.'_mobile_ver2/'.$manager['skin']) && ($CI->storemobileMode || ($CI->mobileMode && $category_config['mobile_version'] == 2) ) ) {
			$board_org_skin = $CI->Boardmanager->board_originalskin_dir.'_mobile_ver2/'.$manager['skin'];//mobile용
		}elseif( is_dir($CI->Boardmanager->board_originalskin_dir.'_mobile/'.$manager['skin']) && ($CI->siteType == 'mobile' ) ) {
			$board_org_skin = $CI->Boardmanager->board_originalskin_dir.'_mobile/'.$manager['skin'];//mobile용
		}else{
			$board_org_skin = $CI->Boardmanager->board_originalskin_dir.$manager['skin'];//pc용
		}

		@mkdir($board_skin);chmod($board_skin,0777);

		/* 스킨파일 복사 */
		$map = directory_map_list(directory_map($board_org_skin,false,false));
		foreach($map as $k=>$v) {
			if(is_dir($board_org_skin.$v)) {
				if(!is_dir($board_skin.$v)) @mkdir($board_skin.$v);
			}
			else{
				@copy($board_org_skin.$v,$board_skin.$v);
			}
			@chmod($board_skin.$v,0777);
		}
	}
}

//skin copy
function getBoardCommentPrenextCopy(){
	$CI =& get_instance();
	$copyfile_comment = '_comment.html';
	$copyfile_prenext = '_prenext.html';
	$copyfile_mobile_file = '_mobile_file.html';
	$board_skin = $CI->Boardmanager->board_skin_dir;
	$category_config = skin_configuration($CI->skin);
	if( ($CI->mobileMode && $category_config['mobile_version'] == 2) ) {
		$board_org_skin = $CI->Boardmanager->board_originalskin_dir.'_mobile_ver2/';//mobile용
	}elseif(  $CI->siteType == 'mobile'  ) {//$this->skin //mobile 인경우
		$board_org_skin = $CI->Boardmanager->board_originalskin_dir.'_mobile/';//mobile용
	}else{
		$board_org_skin = $CI->Boardmanager->board_originalskin_dir;//pc용
	}
	if( is_file($board_org_skin.$copyfile_comment) && !is_file($board_skin.$copyfile_comment)){
		copy($board_org_skin.$copyfile_comment,$board_skin.$copyfile_comment);
		chmod($board_skin.$copyfile_comment,0777);
	}
	if( is_file($board_org_skin.$copyfile_prenext) && !is_file($board_skin.$copyfile_prenext)){
		@copy($board_org_skin.$copyfile_prenext,$board_skin.$copyfile_prenext);
		@chmod($board_skin.$copyfile_prenext,0777);
	}


	if( is_file($board_org_skin.$copyfile_mobile_file) && !is_file($board_skin.$copyfile_mobile_file)){
		@copy($board_org_skin.$copyfile_mobile_file,$board_skin.$copyfile_mobile_file);
		@chmod($board_skin.$copyfile_mobile_file,0777);
	}
}

//첨부파일경로..
function board_upload($filesid,$filename,$folder, $config, $saveFile, $num = 0, $type='board'){

	$CI =& get_instance();
	$config['upload_path'] = $folder;
	if($config['allowed_types']) {
		$config['allowed_types']	= $config['allowed_types'];//'jpg|gif|jpeg|png';
	}else{
		$config['allowed_types']	= '*';
	}
	$config['overwrite']	= TRUE;
	$config['max_size']	= $CI->config_system['uploadLimit'];
	$config['file_name'] = $filename;

	$CI->load->library('upload');
	$CI->upload->initialize($config);

	if ( !$CI->upload->do_upload($filesid, $num)) {//실패시
		$result = array('status' => '0','error' => $CI->upload->display_errors());
	}else{
		@chmod($saveFile, 0777);
		$result = array('status' => 1,'fileInfo'=>$CI->upload->data());
		if($type == 'board' ){//게시판인경우에만 thumb 생성
			$glw = ($CI->manager['gallery_list_w'])?$CI->manager['gallery_list_w']:'250';
			$glh = ($CI->manager['gallery_list_h'])?$CI->manager['gallery_list_h']:'250';
			if($result['fileInfo']['is_image'] == true && $result['fileInfo']['image_width'] > $glw) {//이미지인경우
				$source = $result['fileInfo']['full_path'];
				$target = str_replace($result['fileInfo']['file_name'], '_thumb_'.$result['fileInfo']['file_name'],$result['fileInfo']['full_path']);
				board_image_thumb($source,$target, $glw, $glh);
			}
		}elseif($type == 'promotion' ){//프로모션게시판인경우에만 thumb 생성
			if($result['fileInfo']['is_image'] == true) {//이미지인경우
				$source = $result['fileInfo']['full_path'];
				$target = str_replace($result['fileInfo']['file_name'], '_thumb_'.$result['fileInfo']['file_name'],$result['fileInfo']['full_path']);
				board_image_thumb($source,$target,'250','90',false);
			}
		}
	}
	return $result;
}

//_thumb 생성하기
function board_image_thumb($source,$target,$width,$height,$maintain_ratio=true,$widget=null){
	if(!$widget){
	@ini_set('memory_limit', '5120M');
	@set_time_limit(0);
	}
	$CI =&get_instance();
	$CI->load->library('image_lib');
	$config['image_library']	= 'gd2';
	$config['source_image']	= $source;
	$config['new_image']		= $target;
	$config['maintain_ratio']	= $maintain_ratio;
	$config['width']				= $width;
	$config['height']				= $height;
	$CI->image_lib->initialize($config);
	if ( ! $CI->image_lib->resize())
	{
		$result = array('status' => '0','error' => $CI->image_lib->display_errors());
	}else{
		$result = array('status' => 1);
	}
	$CI->image_lib->clear();
	return $result;
}

// 앞뒤 &nbsp; 제거
function remove_nbsp($value) {
	return trim($value,'&nbsp;');
}

//xcc/csrf
function getcontents($data)
{
	$CI =& get_instance();
	$contents = $data['contents'];

	if( isMobilecheck($data['agent']) && $data['editor'] == 0  && !($data['insert_image'] || $data['upload']) ) {// && $data['mseq'] > 0
		$contents = str_replace($contents,str_ireplace('</p>','[/p]',$contents),$contents);
		$contents = str_replace($contents,str_ireplace('<p>','[p]',$contents),$contents);
		$contents = str_replace($contents,str_ireplace('<br>','[br /]',$contents),$contents);
		$contents = str_replace($contents,str_ireplace('<br />','[br /]',$contents),$contents);
		$contents = str_replace('<','&lt;',$contents);
		$contents = str_replace('>','&gt;',$contents);
		$contents = str_replace('&nbsp;','&amp;nbsp;',$contents);
		$contents = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$contents);
		$contents = str_replace($contents,str_ireplace('[br /]','<br />',$contents),$contents);
		$contents = str_replace($contents,str_ireplace('[p]','<p>',$contents),$contents);
		$contents = str_replace($contents,str_ireplace('[/p]','</p>',$contents),$contents);
	}else{
		$pattern = array
		(
			"'<iframe[^>]*?>'si",
			"'<script[^>]*?>'si",
			"'<meta[^>]*?>'si"
		);

		preg_match_all("/<iframe[^>]*?>/si", $contents, $mat);
		$iframes = $mat[0];
		// iframe을 허용할 도메인을 콤마(,)로 구분해서 등록해 주세요.
		$secuDomain = explode(',','youtube.com,naver.com,daum.net,vimeo.com,ustream.tv,smartucc.kr,google.com,google.co.kr');
		foreach ($iframes as $im)
		{
			foreach ($secuDomain as $dm)
			{
				if(stripos($im,$dm))
				{
					$contents = str_replace($im,str_ireplace('iframe','@IFRAME@',$im),$contents);
					break;
				}
			}
		}

		if($_GET['iframe'] || $_GET['popup']) {//새창 또는 아이프레임일때
			$contents = str_replace('<A href=','<a target="_blank" href=',$contents);
			$contents = str_replace('<a href=','<a target="_blank" href=',$contents);
			$contents = str_replace('<a target="_blank" href="#','<a href="#',$contents);
		}
		$contents = str_replace('< param','<param',$contents);
		$contents = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$contents);
		$contents = preg_replace($pattern,'',$contents);
		$contents = str_replace('@IFRAME@','iframe',$contents);

		$onAttributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$contents = preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $onAttributes) . ")[ \\t\\n]*=/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $contents);
	}

	/* 플래시매직/동영상 치환 {=showDesignFlash(seq)} showDesignVideo */
	$contents =showdesignEditor($contents);

	return $contents;
}

function getDateFormat($d,$f)
{
	$d = date("YmdHis",strtotime($d));
	return $d ? date($f,mktime((int)substr($d,8,2),(int)substr($d,10,2),(int)substr($d,12,2),substr($d,4,2),substr($d,6,2),substr($d,0,4))) : '';
}

//리스트 이미지파일로 등록된 게시글 추출
function getBoardimagefile( &$data ){
	$CI =&get_instance();
	$glw = ($CI->manager['gallery_list_w'])?$CI->manager['gallery_list_w']:'250';
	$glh = ($CI->manager['gallery_list_h'])?$CI->manager['gallery_list_h']:'250';
	/* 게시물 첨부 이미지 */
	foreach($data['result'] as $k=>$v) {
		if($data['result'][$k]['upload']) {
			unset($realfilelist);
			$uploadar = @explode("|",$data['result'][$k]['upload']);
			foreach($uploadar as $filenamear){
				$filelistar = @explode("^^",$filenamear);
				@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;

				if(empty($typefile)) {
				$imagescales = @getImageSize($CI->Boardmodel->upload_path.$realfile);
					$is_image			= ($filetypetmp  && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				}
				if(is_file($CI->Boardmodel->upload_path.$realfile) && $is_image ) {
					/**if(is_file($CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$realfile))) {
						$realfile = '_thumb_'.str_replace("/","",$realfile);
					}
					**/
					if(is_file($CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$realfile))) {
						$realfile = '_thumb_'.str_replace("/","",$realfile);
					}else{
						$uploadthumb = board_image_thumb($CI->Boardmodel->upload_path.str_replace("/","",$realfile),$CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$realfile), $glw, $glh);
						if($uploadthumb['status'])$realfile = '_thumb_'.str_replace("/","",$realfile);
					}
					$data['result'][$k]['filelist'] = array('orignfile'=>$orignalfile,'realfile'=>$realfile,'realthumbfile'=>$CI->Boardmodel->upload_src.$realfile,'sizefile'=>$sizefile,'typefile'=>$typefile,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realsizefile'=>getSizeFormat($sizefile,1),'imagescales'=>$imagescales,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile);
					break;
				}
			}
		}

		if(!($data['result'][$k]['filelist'])) {
			@preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$data['result'][$k]['contents'],$list_image);
			if($list_image[1]) {
				$list_imagear = @explode(" ",$list_image[1]);
				$filenamear = $list_imagear[0];
				$filelistar		= @explode("/",$filenamear);
				$thumbimg	= @end($filelistar);

				/**if(is_file($CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$thumbimg))) {
					$thumbimg = '_thumb_'.str_replace("/","",$thumbimg);
				}

				**/
				if(is_file($CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$thumbimg))) {
					$thumbimg = '_thumb_'.str_replace("/","",$thumbimg);
				}else{
					$uploadthumb = board_image_thumb($CI->Boardmodel->upload_path.str_replace("/","",$thumbimg),$CI->Boardmodel->upload_path.'_thumb_'.str_replace("/","",$thumbimg), $glw, $glh);
					if($uploadthumb['status'])$thumbimg = '_thumb_'.str_replace("/","",$thumbimg);
				}

				$realfile		= (strstr($thumbimg,'_thumb_') && is_file($CI->Boardmodel->upload_path.str_replace('_thumb_','',$thumbimg)))?str_replace('_thumb_','',$thumbimg):$thumbimg;
				$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfile, 'size');
				$orignalfile	= $filenamear;
				$sizefile		= $writefilesize['size'];
				$typefile		= @end(explode('.', $realfile));//확장자추출
				if( $writefilesize ){
					$data['result'][$k]['filelist'] = array('orignfile'=>$orignalfile,'realfile'=>$realfile,'realthumbfile'=>$CI->Boardmodel->upload_src.$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile);
				}else{
					$writefilesize = get_file_info(ROOTPATH.str_replace("../","/",$orignalfile), 'size');
					$orignalfile	= str_replace("../","/",$filenamear);
					$sizefile		= $writefilesize['size'];
					$typefile		= @end(explode('.', $realfile));//확장자추출
					if( $writefilesize ){//다른폴더인경우(이전등의사유로)
						$data['result'][$k]['filelist'] = array('orignfile'=>$CI->protocol.$_SERVER['HTTP_HOST'].$orignalfile,'realfile'=>$CI->protocol.$_SERVER['HTTP_HOST'].$orignalfile);
					}else{
						$data['result'][$k]['filelist'] = array('orignfile'=>$orignalfile,'realfile'=>$orignalfile);
					}
				}
			}
		}
	}
}

//리스트 첨부파일에 등록된 모든것 추출
function getBoardUploadAllfiles( &$datarow ) {
	$CI =&get_instance();
	// faq 첨부파일 리스트 배열만듬
	unset($datarow['filelist']);
	if($datarow['upload']){
		$uploadar = @explode("|",$datarow['upload']);
		foreach($uploadar as $filenamear){
			$filelistar = @explode("^^",$filenamear);
			@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;

			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
				$realfilename = iconv('utf-8','cp949',$realfile);
				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfilename);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				}
				if($is_image && !isMobilecheck($datarow['agent']) )continue;//게시글상세보기시 이미지는 다운로드에서 제외함@2012-12-05
				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfilename, 'size');
					$sizefile		= $writefilesize['size'];
				}
			}

			if(is_file($CI->Boardmodel->upload_path.$realfile) || ( $realfilename && is_file($CI->Boardmodel->upload_path.$realfilename)) ) {
				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfile);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				}
				if($is_image && !isMobilecheck($datarow['agent']) )continue;//게시글상세보기시 이미지는 다운로드에서 제외함@2012-12-05
				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfile, 'size');
					$sizefile		= $writefilesize['size'];
				}

				if(is_array($datarow['filelist'])) {
					$usefile = false;
					foreach($datarow['filelist'] as $realfilenew) {
						if($realfilenew['orignfile'] == $realfile) {$usefile=true;break;}
					}
					if(!$usefile) {
						$datarow['filelist'][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
					}
				} else {
					$datarow['filelist'][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
				}
			}
		}
	}
}

//상세 첨부파일에 등록된 모든것 추출
function getBoardViewUploadAllfiles( &$data ) {
	$CI =&get_instance();
	unset($realfilelist);
	if( defined('__ADMIN__') ) {
		@preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$data['contents'],$list_image);
		foreach($list_image[1] as $allfilenamear){
			$filenamear = @explode(" ",$allfilenamear);
			$filenamear = $filenamear[0];
			$filelistar = @explode("/",$filenamear);
			$thumbimg		= @end($filelistar);
			$realfile		= (strstr($thumbimg,'temp_') && is_file($CI->Boardmodel->upload_path.str_replace('temp_','',$thumbimg)))?str_replace('temp_','',$thumbimg):$thumbimg;
			$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfile, 'size');
			$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfile);
			$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
			$orignalfile	= $filenamear;
			$sizefile		= $writefilesize['size'];
			$typefile		= @end(explode('.', $realfile));//확장자추출
			
			if( !defined('__ADMIN__') && $is_image && !isMobilecheck($data['agent']) ) {
				$filelistname = "filelistimages";
			}else{
				$filelistname = "filelist";
			}
			if( !$thumbimg && is_file($CI->Boardmodel->upload_path.('_thumb_'.$realfile) ) ) $thumbimg = '_thumb_'.$realfile;

			if($writefilesize){
				$data[$filelistname][] = array('orignfile'=>$realfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
			}else{
				$writefilesize = get_file_info(ROOTPATH.$orignalfile, 'size');
				$filetypetmp = @getimagesize(ROOTPATH.$orignalfile);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				$orignalfile	= $filenamear;
				$sizefile		= $writefilesize['size'];
				$typefile		= @end(explode('.', $realfile));//확장자추출
				if( $writefilesize ){//다른폴더인경우(이전등의사유로)
					$data[$filelistname][] = array('orignfile'=>$realfile,'realfilename'=>$realfile,'realfile'=>$orignalfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>ROOTPATH.$orignalfile,'realfileurl'=>$orignalfile,'realthumbfiledir'=>ROOTPATH.$thumbimg,'realthumbfileurl'=>$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
				}
			}
		}
	}

	/**
	//게시글상세보기시 이미지는 다운로드에서 제외함@2012-12-05 관리자는 보임
	**/
	if($data['upload']){
		$uploadar = @explode("|",$data['upload']);
		foreach($uploadar as $filenamear){
			$filelistar = @explode("^^",$filenamear);
			@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;
			if (!defined('__ADMIN__') && $typefile=="image/psd") $typefile = "psd";

			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
				$realfilename = iconv('utf-8','cp949',$realfile);
				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfilename);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				}
				
				if( !defined('__ADMIN__') && $is_image && !isMobilecheck($data['agent']) ) {
					$filelistname = "filelistimages"; 
				}else{
					$filelistname = "filelist";
				}

				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfilename, 'size');
					$sizefile		= $writefilesize['size'];
				}
			}

			if(is_file($CI->Boardmodel->upload_path.$realfile) || ( $realfilename && is_file($CI->Boardmodel->upload_path.$realfilename)) ) {
				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfile);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				} 
				if( !defined('__ADMIN__') && $is_image && !isMobilecheck($data['agent']) ) {
					$filelistname = "filelistimages"; 
				}else{
					$filelistname = "filelist";
				}

				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfile, 'size');
					$sizefile		= $writefilesize['size'];
				}
				if( !$thumbimg && is_file($CI->Boardmodel->upload_path.('_thumb_'.$realfile) ) ) $thumbimg = '_thumb_'.$realfile;

				if(is_array($data[$filelistname])) {
					$usefile = false;
					foreach($data[$filelistname] as $realfilenew) {
						if($realfilenew['orignfile'] == $realfile) {$usefile=true;break;}
					}
					if(!$usefile) {
						if( defined('__ADMIN__') ) {
							$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
						}else{
							$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
						}
					}
				} else {
					if( defined('__ADMIN__') ){
						$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
					}else{
						$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
					}
				}
			}
		}
	}

	if($data['re_upload']){
		$uploadar = @explode("|",$data['re_upload']);
		foreach($uploadar as $filenamear){
			$filelistar = @explode("^^",$filenamear);
			@list($realfile, $orignalfile, $sizefile, $typefile) = $filelistar;

			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$realfile)) {//데이타이전->한글파일명처리
				$realfilename = iconv('utf-8','cp949',$realfile);
				
				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfilename);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				} 
				if( !defined('__ADMIN__') && $is_image && !isMobilecheck($data['agent']) ) {
					$filelistname = "filelistimages";
					//continue;//게시글상세보기시 이미지는 다운로드에서 제외함@2012-12-05
				}else{
					$filelistname = "filelist";
				} 

				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfilename, 'size');
					$sizefile		= $writefilesize['size'];
				}
			}

			if(is_file($CI->Boardmodel->upload_path.$realfile) || ( $realfilename && is_file($CI->Boardmodel->upload_path.$realfilename)) ) {

				if(empty($typefile)) {
				$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$realfile);
				$is_image			= ($filetypetmp && !($filetypetmp[2] == 4 || $filetypetmp[2] == 5) )?1:0;
				}else{  
					$is_image			= ( preg_match("/image/",$typefile) )?1:0;
				}

				if( !defined('__ADMIN__') && $is_image && !isMobilecheck($data['agent']) ) {
					$filelistname = "filelistimages"; 
				}else{
					$filelistname = "filelist";
				}

				if(empty($typefile)) {
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $realfile));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}
				}

				if(!$sizefile) {
					$realfile_temp			= @explode("/",$realfile);
					$orignalfile		= @end($realfile_temp);
				}

				if(!$sizefile) {
					$writefilesize = get_file_info($CI->Boardmodel->upload_path.$realfile, 'size');
					$sizefile		= $writefilesize['size'];
				}
				if( !$thumbimg && is_file($CI->Boardmodel->upload_path.('_thumb_'.$realfile) ) ) $thumbimg = '_thumb_'.$realfile;

				if(is_array($data[$filelistname])) {
					$usefile = false;
					foreach($data[$filelistname] as $realfilenew) {
						if($realfilenew['orignfile'] == $realfile) {$usefile=true;break;}
					}
					if(!$usefile) {
						if( defined('__ADMIN__')) {
							$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
						}else{
							$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
						}
					}
				} else {
					if( defined('__ADMIN__')) {
						$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1));
					}else{
						$data[$filelistname][] = array('orignfile'=>$orignalfile,'realfilename'=>$realfile,'realfile'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfile'=>$thumbimg,'sizefile'=>$sizefile,'typefile'=>$typefile,'is_image'=>$is_image,'realfiledir'=>$CI->Boardmodel->upload_path.$realfile,'realfileurl'=>$CI->Boardmodel->upload_src.$realfile,'realthumbfiledir'=>$CI->Boardmodel->upload_path.$thumbimg,'realthumbfileurl'=>$CI->Boardmodel->upload_src.$thumbimg,'realsizefile'=>getSizeFormat($sizefile,1),'imagesize'=>$filetypetmp);
					}
				}
			}
		}
	}

}

/* 게시글이동/복사 이미지 파일경로보정 */
function adjustMoveCopyImages(&$contents, $orignaldir, $savedir){

	$orignaldir = ($orignaldir)?$orignaldir:BOARDID;
	//debug_var($orignaldir);

	if(preg_match_all("/[\"|']?\/(data\/board\/".$orignaldir."\/[^\"']+)[\"|']?/",$contents,$matches)){
		foreach($matches[1] as $tPath){
			// 에디터파일 경로
			$dPath = preg_replace("/data\/board\/".$orignaldir."\//",$savedir,$tPath);

			// 정규식 문자열처리
			$tPathForReg = str_replace(array("/","."),array("\/","\."),$tPath);

			// 보정
			$contents = preg_replace("/\/".$tPathForReg."/",$dPath,$contents);
		}
	}
	return $contents;
}


/* 상품후기 카운트 증가/차감 */
function goods_review_count($data, $newseq, $type='plus')
{
	if ( $newseq && $data['goods_seq'] )  {
		$CI =& get_instance();
		$CI->load->model('goodsmodel');
		$CI->goodsmodel->goods_review_count($data['goods_seq'],$type);
	}
}

/* 상품후기 카운트 증가/차감 */
function goods_qna_count($data, $newseq, $type='plus')
{
	if ( $newseq && $data['goods_seq'] )  {
		$CI =& get_instance();
		$CI->load->model('goodsmodel');
		$CI->goodsmodel->goods_qna_count($data['goods_seq'],$type);
	}
}


/* 첨부파일 추가 */
function board_mobile_file($parentdata, &$realfilename, &$incimage)
{
	$CI =& get_instance();

	if( $_FILES ) {
		set_time_limit(0);
		ini_set('memory_limit', '3500M');
	}
	//이미등록된 첨부파일 변경시
	if(!empty($_POST['orignfile_info'])){
		$oldfile = @explode("|",$parentdata['upload']);
		for ( $num=0;$num<count($_POST['orignfile_info']);$num++) {
			$_POST['orignfile_info'][$num] = str_replace("/data/board/".BOARDID."/","",$_POST['orignfile_info'][$num]);
			$oldrealfile = @explode("^^",$_POST['orignfile_info'][$num]);
			if(@in_array($_POST['orignfile_info'][$num],$oldfile) && @is_file($CI->Boardmodel->upload_path.$oldrealfile[0]) && is_uploaded_file($_FILES['file_info']['tmp_name'][$num]) ){//기존위치에 수정시 변경

				if( ! empty($_FILES['file_info'][$num])){
					$folder			= $CI->Boardmodel->upload_path;
					$tmpname	= $value['tmp_name'][$num];
					$file_ext		= end(explode('.', $_FILES['file_info'][$num]));//확장자추출
					$file_name	= str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
					$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
					$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
					$saveFile		= $folder.$file_name;
					$tmp = @getimagesize($value['tmp_name'][$num]);
					$_FILES['Filedata']['type'] = $tmp['mime'];

					$fileresult = board_upload('file_info', $file_name, $folder, $conf, $saveFile, $num);//status  error, fileInfo
					if( $fileresult['status'] == 1 ) {

						if(is_array($realfilename)) {
							$usefile = false;
							foreach($realfilename as $realfile) {
								$realfilear = @explode("^^",$realfile);
								if($realfilear[0] == $file_name) {$usefile=true;break;}
							}
							if(!$usefile) {
								$realfilename[] = $file_name."^^".$_FILES['file_info'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];

								//모바일기기인 경우 이미지본문에 첨부하기
								if( $_POST['insert_image'] && $tmp['mime'] ){
									$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$file_name.'" '.$tmp['3'].' class="txc-image" alt="" /><br /><br />';
								}
							}
						} else {
							$realfilename[] = $file_name."^^".$_FILES['file_info'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];

							//모바일기기인 경우 이미지본문에 첨부하기
							if( $_POST['insert_image'] && $tmp['mime'] ){
								$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$file_name.'" '.$tmp['3'].'  class="txc-image"  alt="" /><br /><br />';
							}
						}
					}
				}
			}

			elseif ( @in_array($_POST['orignfile_info'][$num],$oldfile) && @is_file($CI->Boardmodel->upload_path.$oldrealfile[0]) && !is_uploaded_file($_FILES['file_info']['tmp_name'][$num]) ) {
				$realfilename[] = $_POST['orignfile_info'][$num];

				//모바일기기인 경우 이미지본문에 첨부하기
				$tmp = @getimagesize($CI->Boardmodel->upload_path.$oldrealfile[0]);
				if( $_POST['insert_image'] && $tmp['mime'] ){
					$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$oldrealfile[0].'"  '.$tmp['3'].' class="txc-image"  alt="" /><br /><br />';
				}
			}
		}
	}

	//새로등록하는 첨부파일용
	foreach($_FILES as $key => $value)
	{
		for ( $num=0;$num<count($_FILES['file_info']['name']);$num++) {
			if( ! empty($value['name'][$num])){
				$folder			= $CI->Boardmodel->upload_path;
				$tmpname	= $value['tmp_name'][$num];
				$file_ext		= end(explode('.', $value['name'][$num]));//확장자추출
				$file_name	= str_replace(" ", "", (substr(microtime(), 2, 6))).'.'.$file_ext;
				$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
				$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
				$saveFile		= $folder.$file_name;

				$tmp = @getimagesize($value['tmp_name'][$num]);
				if(!$tmp['mime']){
					$_FILES['Filedata']['type'] = $file_ext;//확장자추출
				}else{
					$_FILES['Filedata']['type'] = $tmp['mime'];
				}

				$fileresult = board_upload($key, $file_name, $folder, $conf, $saveFile, $num);//status  error, fileInfo
				if( $fileresult['status'] == 1 ) {

					if(is_array($realfilename)) {
						$usefile = false;
						foreach($realfilename as $realfile) {
							$realfilear = @explode("^^",$realfile);
							if($realfilear[0] == $file_name) {$usefile=true;break;}
						}
						if(!$usefile) {
							$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];

							//모바일기기인 경우 이미지본문에 첨부하기
							if( $_POST['insert_image'] && $tmp['mime'] ){
								$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$file_name.'" '.$tmp['3'].' class="txc-image"  alt="" /><br /><br />';
							}
						}
					} else {
						$realfilename[] = $file_name."^^".$value['name'][$num]."^^".$value['size'][$num]."^^".$value['type'][$num];

						//모바일기기인 경우 이미지본문에 첨부하기
						if( $_POST['insert_image'] && $tmp['mime'] ){
							$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$file_name.'" '.$tmp['3'].' class="txc-image"  alt="" /><br /><br />';
						}
					}
				}
			}
		}
	}

	//에딧터이미지파일용
	$filenamenumber=0;
	if(!empty($_POST['tx_attach_files'])) {
		if(is_array($_POST['tx_attach_files'])){
				//array_unique($_POST['tx_attach_files']);array_unique($_POST['tx_attach_files_name']);
			foreach($_POST['tx_attach_files'] as $tx_attach_file){
				//$editerimg = preg_replace("/^\/data\/tmp\//","",$tx_attach_file);
				$editerimg = end(explode('/', $tx_attach_file));//확장자추출
				if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$editerimg)) {//데이타이전->한글파일명처리
					$editerimgkorean = iconv('utf-8','cp949',$editerimg);
				}

				$client_name = ($_POST['tx_attach_files_name'][$filenamenumber])?$_POST['tx_attach_files_name'][$filenamenumber]:$editerimg;
				if( @is_file($CI->Boardmodel->upload_path.$editerimg) ||  @is_file($CI->Boardmodel->upload_path.$editerimgkorean) ) {

					@rename($CI->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$CI->Boardmodel->upload_path.'/'.'_thumb_'.$editerimg);//파일복사
					@rename($CI->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$CI->Boardmodel->upload_path.'/'.'_thumb_'.$editerimgkorean);//파일복사

					$filesizetmp = @filesize($CI->Boardmodel->upload_path.$editerimg);
					$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$editerimg);
					if(!$filetypetmp['mime']){
						$typefile =end(explode('.', $editerimg));//확장자추출
					}else{
						$typefile =$filetypetmp['mime'];
					}


					if(is_array($realfilename)) {
						$usefile = false;
						foreach($realfilename as $realfile) {
							$realfilear = @explode("^^",$realfile);
							if($realfilear[0] == $editerimg) {$usefile=true;break;}
						}
						if(!$usefile) {
							$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;

							//모바일기기인 경우 이미지본문에 첨부하기
							if( $_POST['insert_image'] && $filetypetmp['mime'] ){
								$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$editerimg.'" '.$filetypetmp['3'].' class="txc-image"  alt="" /><br /><br />';
							}
						}
					} else {
						$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;

						//모바일기기인 경우 이미지본문에 첨부하기
						if( $_POST['insert_image'] && $filetypetmp['mime'] ){
							$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$editerimg.'" '.$filetypetmp['3'].' class="txc-image"  alt="" /><br /><br />';
						}
					}
				}else{
					if ( @is_file($CI->Boardmanager->board_tmp_dir.$editerimg) || @is_file($CI->Boardmanager->board_tmp_dir.$editerimgkorean) ) {
						@rename($CI->Boardmanager->board_tmp_dir.$editerimg,$CI->Boardmodel->upload_path.'/'.$editerimg);//파일복사
						@rename($CI->Boardmanager->board_tmp_dir.'_thumb_'.$editerimg,$CI->Boardmodel->upload_path.'/'.'_thumb_'.$editerimg);//파일복사


						@rename($CI->Boardmanager->board_tmp_dir.$editerimgkorean,$CI->Boardmodel->upload_path.'/'.$editerimgkorean);//파일복사
						@rename($CI->Boardmanager->board_tmp_dir.'_thumb_'.$editerimgkorean,$CI->Boardmodel->upload_path.'/'.'_thumb_'.$editerimgkorean);//파일복사


						$filesizetmp = @filesize($CI->Boardmodel->upload_path.$editerimg);
						$filetypetmp = @getimagesize($CI->Boardmodel->upload_path.$editerimg);
						if(!$filetypetmp['mime']){
							$typefile =end(explode('.', $editerimg));//확장자추출
						}else{
							$typefile =$filetypetmp['mime'];
						}

						if(is_array($realfilename)) {
							$usefile = false;
							foreach($realfilename as $realfile) {
								$realfilear = @explode("^^",$realfile);
								if($realfilear[0] == $editerimg) {$usefile=true;break;}
							}
							if(!$usefile) {
								$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
								//모바일기기인 경우 이미지본문에 첨부하기
								if( $_POST['insert_image'] && $filetypetmp['mime'] ){
									$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$editerimg.'" '.$filetypetmp['3'].' class="txc-image"  alt="" /><br /><br />';
								}
							}
						} else {
							$realfilename[] = $editerimg."^^".$client_name."^^".$filesizetmp."^^".$typefile;
							//모바일기기인 경우 이미지본문에 첨부하기
							if( $_POST['insert_image'] && $filetypetmp['mime'] ){
								$incimage[] = '<img src="'.$CI->Boardmodel->upload_src.$editerimg.'" '.$filetypetmp['3'].' class="txc-image"  alt="" /><br /><br />';
							}
						}
					}
				}
				$filenamenumber++;
			}
		}
	}
}

	
function boardcaptcha($refresh=null) {
	
	$CI =& get_instance();
	$CI->load->model('Captchamodel');
	$CI->protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https://' : 'http://'; 
	$CI->load->library('antispam');
	$configs = array(
			'img_path' => $CI->Boardmanager->board_capt_dir,
			'img_url' => $CI->protocol.$_SERVER['HTTP_HOST'].$CI->Boardmanager->board_capt_src,
			'expiration' => 7200,
			'font_path' => $CI->Boardmanager->board_captcha_ttf_new,
			'img_width' => 155,
			'img_height' => 30
		);
	$cap = $CI->antispam->get_antispam_image($configs); 
	
	if(!empty($cap)) {

		if($refresh) {
			// First, delete old captchas
			$expiration = time(); // Two hour limit
			$CI->Captchamodel->data_delete($expiration,$CI->input->ip_address());
		}

		$data = array(
		'captcha_time' => $cap['time'],
		'ip_address' => $CI->input->ip_address(),
		'word' => $cap['word']
		);
		$CI->Captchamodel->data_write($data);
	}
	return $cap;
}

function boardalllist(){
	$CI =& get_instance();
	$thisfile = $CI->uri->rsegments[count($CI->uri->rsegments)];
	if( $thisfile == 'manager_reg' ) $sc['manager_setting'] = 1;
	
	$sc['select']		= ' seq, id, name, totalnum ';
	if( $thisfile == 'write' ) {
		if(!in_array(BOARDID, $CI->Boardmanager->renewlist) ) {//기본만
			$sc['whereis']	= ' and id not in (\''.@implode("','",$CI->Boardmanager->renewlist).'\') ';
			if ( SERVICE_CODE == 'P_STOR' ) {
				$sc['whereis']	.= ' and id not in ("goods_qna","goods_review") ';//기본
			}else{
				$sc['whereis']	.= ' and id not in ("store_review","store_reservation","store_gallery") ';//기본
			}
			$CI->boardmanagerlist = $CI->Boardmanager->manager_whereis_list($sc);
		}
	}elseif( $thisfile == 'main' ){
		$sc['whereis']	= ' and skin_type = "default" ';//기본
			if ( SERVICE_CODE == 'P_STOR' ) {
				$sc['whereis']	.= ' and id not in ("goods_qna","goods_review") ';//기본
			}else{
				$sc['whereis']	.= ' and id not in ("store_review","store_reservation","store_gallery") ';//기본
			}
		$CI->boardmanagerlist = $CI->Boardmanager->manager_whereis_list($sc);//게시판정보
	}else{
			if ( SERVICE_CODE == 'P_STOR' ) {
				$sc['whereis']	= ' and id not in ("goods_qna","goods_review") ';//기본
			}else{
				$sc['whereis']	= ' and id not in ("store_review","store_reservation","store_gallery") ';//기본
			}
		$CI->boardmanagerlist = $CI->Boardmanager->manager_whereis_list($sc);//게시판정보
		 
		if( $thisfile == 'manager_reg' ) {
			if( $_GET['manager_seq'] ){
				//$CI->load->model('boardadmin'); 
				foreach($CI->boardmanagerlist as $k=>$boardinfo) {
					$adsc['boardid']				= $boardinfo['id'];
					$adsc['manager_seq']	= $_GET['manager_seq']; 
					$boardadmin = $CI->boardadmin->get_data($adsc);
					if($boardadmin){
						if($boardadmin['board_view'] == 2 ) {
							$CI->boardmanagerlist[$k]['board_view_pw'] = 2;
						}
						$CI->boardmanagerlist[$k]['board_view']	= $boardadmin['board_view'];
						$CI->boardmanagerlist[$k]['board_act']		= $boardadmin['board_act'];
					}else{//설정전에는 모든권한 부여함
						$board_view = $CI->authmodel->manager_limit_act('board_view');
						$board_act = $CI->authmodel->manager_limit_act('board_act'); 
						if( $board_view ) {
							$CI->boardmanagerlist[$k]['board_view_pw']	= 1;
							$CI->boardmanagerlist[$k]['board_view']		= 1;
						}
						if( $board_act )  $CI->boardmanagerlist[$k]['board_act']			= 1;
					}
				}
			}
		}
	}
	$CI->template->assign('boardmanagerlist',$CI->boardmanagerlist);//게시판복사목록
}

/**
*@ 게시물 보기 board_view getmanagerauth('board_view')
*@ 게시물 등록/답변/삭제 board_act getmanagerauth('board_act')
*@ 게시판 관리 (생성,수정,삭제) board_manger getmanagerauth('board_manger')
**/
function getmanagerauth($boardtype,$data=null) {
	$CI =& get_instance();

	if( defined('__ADMIN__') ) {//관리자페이지
		/* 관리자 권한 체크 : 시작 */
		$auth = $CI->authmodel->manager_limit_act($boardtype); 
		if( ( $boardtype == 'board_list' || $boardtype == 'board_view' ||  $boardtype == 'board_act')   ) { 
			$adsc['boardid']				= (BOARDID)?BOARDID:$_GET['id'];
			$adsc['manager_seq']		= $CI->managerInfo['manager_seq']; 
			$boardadmin = $CI->boardadmin->get_data($adsc); 
			$boardtypepage = ($boardtype == 'board_list')?'board_view':$boardtype; 
			if($boardadmin) $boardadmin[$boardtypepage] = (int) $boardadmin[$boardtypepage]; 
			//보기 권한 및 비밀글포함여부체크  
			if( $boardadmin && $auth && ( $boardadmin[$boardtypepage] == '0' || (($boardtype == 'board_list') && $CI->manager['auth_read'] != '[all]'  && $boardadmin[$boardtypepage] < 1)  || ($boardtype == 'board_view'&& (($data['hidden'] == 1 && $boardadmin[$boardtypepage] != 2) || ($boardadmin[$boardtypepage]<1) )  )) ) unset($auth);// && $CI->manager['auth_write'] != '[all]'  
		}

		if(!$auth){ 
			if( $boardtype == 'board_view' ||  $boardtype == 'board_act') { 
				openDialogAlert($CI->auth_msg,400,140,'parent',"$('#dlg').dialog('close');");
				exit;
			}
			$callback = "history.go(-1);"; 
			$CI->template->assign(array('auth_msg'=>$CI->auth_msg,'callback'=>$callback,'boardtype'=>$boardtype));
			$CI->template->define(array('denined'=>$CI->skin.'/common/denined.html'));
			$CI->template->print_("denined");
			exit;
		}
		/* 관리자 권한 체크 : 끝 */
	}else{//사용자페이지 
		if( defined('__ISADMIN__') && ($boardtype == 'board_list' || $boardtype == 'board_view' || $boardtype == 'board_act') ) {//전체접근이면 부관리자체크제외 
			$adsc['boardid']				= (BOARDID)?BOARDID:$_GET['id'];
			$adsc['manager_seq']		= $CI->managerInfo['manager_seq']; 
			$boardtypepage = ($boardtype == 'board_list')?'board_view':$boardtype; 
			$boardadmin = $CI->boardadmin->get_data($adsc); 
			if( $boardadmin && ( $boardadmin[$boardtypepage] == '0' || (($boardtype == 'board_list') && $CI->manager['auth_read'] != '[all]'  && $boardadmin[$boardtypepage] < 1)  || ($boardtype == 'board_view' && (($data['hidden'] == 1 && $boardadmin[$boardtypepage] != 2) || ($boardadmin[$boardtypepage]<1) )  )) ) {//&& $CI->manager['auth_write'] != '[all]'  
				pageBack('권한이 없습니다.');exit;  
			} 
		}
	}
}

/** 
* 게시판의 아이콘 적용
* @ new hot admin review
**/
function getboardicon() 
{
	$CI =& get_instance();

	$icon1array = array("image","file","video","mobile","new","hot","review","award","admin","hidden");
	 foreach($icon1array as $icontype1) {
		 if($icontype1 == 'image' ) {
			$CI->{'icon_'.$icontype1.'_img'} = $CI->Boardmanager->img_icon_src;
		 }else{
			//$CI->{'icon_'.$icontype1.'_img'} = $CI->Boardmanager->{$icontype1.'_icon_src'};
			if ($icontype1=="admin") {
				$board_id = $CI->manager['id'];
			} else {
				$board_id = BOARDID;
			}
			$CI->{'icon_'.$icontype1.'_img'} = ($CI->manager['icon_'.$icontype1.'_img'] && is_file($CI->Boardmanager->board_data_dir.$board_id.'/'.$CI->manager['icon_'.$icontype1.'_img']) ) ? $CI->Boardmanager->board_data_src.$board_id.'/'.$CI->manager['icon_'.$icontype1.'_img']:$CI->Boardmanager->{$icontype1.'_icon_src'};
		 }
		$CI->manager['icon_'.$icontype1.'_src'] = $CI->{'icon_'.$icontype1.'_img'};
		$CI->manager['icon_'.$icontype1.'_img'] = $CI->{'icon_'.$icontype1.'_img'}; 
	 }

	 $icon1array = array("cmt_reply","notice","re","blank","snst","snst","snst","snst","snst");
	 foreach($icon1array as $icontype1) {
		$CI->{$icontype1.'_img'} = ($CI->manager['icon_'.$icontype1.'_img'] && is_file($CI->Boardmanager->upload_path.$CI->manager['icon_'.$icontype1.'_img']) ) ? $CI->Boardmanager->board_data_src.BOARDID.'/'.$CI->manager['icon_'.$icontype1.'_img']:$CI->Boardmanager->{$icontype1.'_icon_src'};
		$CI->manager[$icontype1.'_src'] = $CI->{'icon_'.$icontype1.'_img'};
		$CI->manager[$icontype1.'_img'] = $CI->{'icon_'.$icontype1.'_img'}; 
	 }

	//게시글/댓글 평가 아이콘 
	if( defined('__ADMIN__') && in_array('manager_write',$CI->uri->rsegments) ) { 
		$icon2array = array("recommend","none_rec","recommend1","recommend2","recommend3","recommend4","recommend5","cmt_recommend","cmt_none_rec"); 
		foreach($icon2array as $icontype2) {
			$CI->{'icon_'.$icontype2.'_img'} = ($CI->manager['icon_'.$icontype2.'_img'] &&  is_file($CI->Boardmanager->board_data_dir.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']) )?$CI->Boardmanager->board_data_src.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']:$CI->Boardmanager->{$icontype2.'_icon_src'};
			$CI->manager['icon_'.$icontype2.'_src'] = $CI->{'icon_'.$icontype2.'_img'};
			$CI->manager['icon_'.$icontype2.'_img'] = $CI->{'icon_'.$icontype2.'_img'};  
		 }  
	}else{
		if( $CI->manager['auth_recommend_use'] == 'Y' ) {
			if( $CI->manager['recommend_type'] == '3' ) {
				$icon2array = array("recommend1","recommend2","recommend3","recommend4","recommend5");
			}elseif($CI->manager['recommend_type'] == '2'){
				$icon2array = array("recommend","none_rec");
			}elseif($CI->manager['recommend_type'] == '1'){ 
				$icon2array = array("recommend");
			}
			foreach($icon2array as $icontype2) {
				$CI->{'icon_'.$icontype2.'_img'} = ($CI->manager['icon_'.$icontype2.'_img'] &&  is_file($CI->Boardmanager->board_data_dir.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']) )?$CI->Boardmanager->board_data_src.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']:$CI->Boardmanager->{$icontype2.'_icon_src'};
				$CI->manager['icon_'.$icontype2.'_src'] = $CI->{'icon_'.$icontype2.'_img'};
				$CI->manager['icon_'.$icontype2.'_img'] = $CI->{'icon_'.$icontype2.'_img'};  
			 }
		}

		if( $CI->manager['auth_cmt_recommend_use'] == 'Y' ) {
			$icon2array = array("cmt_recommend","cmt_none_rec");
			foreach($icon2array as $icontype2) {
				$CI->{'icon_'.$icontype2.'_img'} = ($CI->manager['icon_'.$icontype2.'_img'] &&  is_file($CI->Boardmanager->board_data_dir.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']) )?$CI->Boardmanager->board_data_src.BOARDID.'/'.$CI->manager['icon_'.$icontype2.'_img']:$CI->Boardmanager->{$icontype2.'_icon_src'};
				$CI->manager['icon_'.$icontype2.'_src'] = $CI->{'icon_'.$icontype2.'_img'};
				$CI->manager['icon_'.$icontype2.'_img'] = $CI->{'icon_'.$icontype2.'_img'};  
			 }
		}
	} 
}


// 게시판 > 게시글 , 관리자 > 메인화면 추출용
function getAdminBoardWidgets($bdwidget, & $widgetloop, & $name, & $totalcount)
{
	$CI =& get_instance();
	unset($CI->wigetBoardmodel,$CI->widgetboardid);
	$CI->widgetboardid = $bdwidget['boardid'];

	//if(!$CI->text) $CI->load->helper('text');//strcut
	if(!$CI->Boardmanager) $CI->load->model('Boardmanager');
	if(!$CI->membermodel) $CI->load->model('membermodel'); 
	if(!$CI->boardadmin) $CI->load->model('boardadmin'); 

	$CI->mainboardclass = (uri_string() == 'admin/main/index')?"":" blue ";

	if( $CI->widgetboardid == 'goods_qna' ) {
		$CI->load->model('Goodsqna','qnaBoardmodel');
		$CI->wigetBoardmodel = $CI->qnaBoardmodel;
	}elseif( $CI->widgetboardid == 'goods_review' ) {
		$CI->load->model('Goodsreview','reviewBoardmodel');
		$CI->wigetBoardmodel = $CI->reviewBoardmodel;
		$qry = "select * from fm_boardform  where boardid='".$CI->widgetboardid."'  order by sort_seq, boardid asc";
		$query = $CI->db->query($qry);
		$user_arr = $query -> result_array();
		unset($goodsreview_sub);
		foreach ($user_arr as $user){
			$user['label_ctype'] = $CI->Boardmanager->typeNames[$user['label_type']];
			$goodsreview_sub[] = $user;
		}
		$CI->template->assign('goodsreview_sub', $goodsreview_sub);
	}elseif( $CI->widgetboardid == 'bulkorder' ) {
		$CI->load->model('Boardbulkorder','bulkBoardmodel');
		$CI->wigetBoardmodel = $CI->bulkBoardmodel;
		//대량구매 추가양식 정보
		$qry = "select * from fm_boardform  where boardid='".$CI->widgetboardid."'  order by sort_seq, boardid asc";
		$query = $CI->db->query($qry);
		$user_arr = $query -> result_array();
		foreach ($user_arr as $user){
			$user['label_ctype'] = $CI->Boardmanager->typeNames[$user['label_type']];
			$bulkorder_sub[] = $user;
		}
		$CI->template->assign('bulkorder_sub', $bulkorder_sub);
	}else{
		$CI->load->model('Boardmodel');
		$CI->wigetBoardmodel = $CI->Boardmodel;
	}
	$CI->load->model('Boardindex');


	$querystr = '';
	$sc['whereis']	= ' and id= "'.$CI->widgetboardid.'" ';
	$sc['select']		= ' * ';
	$CI->manager = $CI->Boardmanager->managerdataidck($sc);//게시판정보
	if(!$CI->manager){return;}
	$name = $CI->manager['name'];
	$totalcount = $CI->manager['totalnum'];


	$CI->wigetBoardmodel->upload_path		= $CI->Boardmanager->board_data_dir.$CI->widgetboardid.'/';
	$CI->wigetBoardmodel->upload_src		= $CI->Boardmanager->board_data_src.$CI->widgetboardid.'/';

	$CI->boardurl->lists		= $CI->Boardmanager->realboarduserurl.$CI->widgetboardid.$querystr;				//게시물관리

	$CI->boardurl->write		= $CI->Boardmanager->realboardwriteurl.$CI->widgetboardid.$querystr;				//게시물등록
	$CI->boardurl->modify	= $CI->Boardmanager->realboardwriteurl.$CI->widgetboardid.$querystr.'&seq=';	//게시물수정
	$CI->boardurl->view		= $CI->Boardmanager->realboardviewurl.$CI->widgetboardid.$querystr.'&seq=';	//게시물보기
	$CI->boardurl->reply		= $CI->Boardmanager->realboardwriteurl.$CI->widgetboardid.$querystr.'&reply=y&seq=';	//게시물답변

	$CI->boardurl->perm		= $CI->Boardmanager->realboardpermurl.$CI->widgetboardid.'&returnurl=';						//접근권한
	$CI->boardurl->pw			= $CI->Boardmanager->realboardpwurl.$CI->widgetboardid.'&returnurl=';						//접근권한
	
	getboardicon();

	$boardurl = $CI->boardurl;

	/**
	 * icon setting
	**/
	$CI->icon_new_img			= ($CI->manager['icon_new_img'] && @is_file($CI->wigetBoardmodel->upload_path.$CI->manager['icon_new_img']) ) ? $CI->Boardmanager->board_data_src.$CI->widgetboardid.'/'.$CI->manager['icon_new_img'].'?'.time():$CI->Boardmanager->new_icon_src;//newicon
	$CI->icon_hot_img			= ($CI->manager['icon_hot_img'] && @is_file($CI->wigetBoardmodel->upload_path.$CI->manager['icon_hot_img']) ) ? $CI->Boardmanager->board_data_src.$CI->widgetboardid.'/'.$CI->manager['icon_hot_img'].'?'.time():$CI->Boardmanager->hot_icon_src;//hoticon

	$CI->icon_review_img			= ($CI->manager['icon_review_img'] && @is_file($CI->wigetBoardmodel->upload_path.$CI->manager['icon_review_img']) ) ? $CI->Boardmanager->board_data_src.$CI->widgetboardid.'/'.$CI->manager['icon_review_img'].'?'.time():$CI->Boardmanager->review_icon_src;//hoticon

	get_auth($CI->manager, '', 'read', $isperm);//접근권한체크
	$CI->manager['isperm_read'] = ($isperm['isperm_read'] === true)?'':'_no';
	$CI->manager['fileperm_read']= (isset($isperm['fileperm_read']))?$isperm['fileperm_read']:'';

	get_auth($CI->manager, '', 'write', $isperm);//접근권한체크
	$CI->manager['isperm_write'] = ($isperm['isperm_write'] === true)?'':'_no';

	/**
	 * notice setting
	**/
	$idxsc['boardid']			= $CI->widgetboardid;
	$idxsc['orderby']			= 'gid';
	$idxsc['sort']				= 'asc';
	$idxsc['page']				= '0';
	$idxsc['perpage']			= $bdwidget['limit'];
	$wdata = $CI->Boardindex->idx_list_search($idxsc);//최근게시글

	$idx = $CI->manager['totalnum'];
	foreach($wdata['result'] as $wdatarow){
		$widgetsql['whereis']	= ' and gid= "'.$wdatarow['gid'].'"';

		if( $CI->widgetboardid == 'goods_qna') {
			$widgetsql['select']		= ' seq, depth, hit, subject, upload, r_date, m_date,  category, comment,  mid, mseq, name , hidden, display, depth,  goods_seq, re_contents, agent, contents ';
			$widget = $CI->wigetBoardmodel->get_data($widgetsql);//게시판목록

		}elseif($CI->widgetboardid == 'goods_review' ) {
			$widgetsql['select']		= ' seq, depth, hit, subject, upload, r_date, m_date,  category, comment, score, emoney,  mid, mseq, name , hidden, display, depth,  goods_seq, agent, contents ';
			$widget = $CI->wigetBoardmodel->get_data($widgetsql);//게시판목록

		}elseif($CI->widgetboardid == 'bulkorder' ) {
			$widgetsql['select']		= ' seq, hit, subject, upload, r_date, m_date,  category, comment,  mid, mseq, name , hidden, display, depth, re_contents, agent, contents ';
			$widget = $CI->wigetBoardmodel->get_data($widgetsql);//게시판목록

		}else{
			$widgetsql['whereis']	.= ' and boardid = "'.$CI->widgetboardid.'" ';
			$widgetsql['select']		= ' seq, hit, subject, upload, r_date, m_date,  category, comment,  mid, mseq, name , hidden, display, depth, re_contents, agent, contents ';
			$widget = $CI->wigetBoardmodel->get_data($widgetsql);//게시판목록
		}

		if(isset($widget['seq'])) {

			$widget['number']			= $idx;//번호
			$widget['real_category']	= getstrcut(strip_tags($widget['category']), 7);
			$widget['category']			= (!empty($widget['category']) )? ' <span class="cat">['.$widget['category'].']</span>':'';

			getminfo($CI->manager, $widget, $mdata, $boardname);//회원정보
			$widget['name'] = $boardname;
			$widget['reply_title']		= ($widget['re_contents'])?'<span class="blue" >답변완료</span>':'<span class="gray" >답변대기</span>';//상태

			if($CI->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$CI->manager['icon_new_day'].' day '.substr(str_replace("-","",$widget['m_date']),0,8))) >= date("Ymd") ) {//new
				$widget['iconnew']	= ' <img src="'.$CI->icon_new_img.'" title="new" > ';
			}

			if($CI->manager['icon_hot_visit'] > 0 && $CI->manager['icon_hot_visit'] <= $widget['hit'] ) {//조회수
				$widget['iconhot']		= ' <img src="'.$CI->icon_hot_img.'" title="hot" > ';
			}
			if( getBoardFileck($widget['upload'], $widget['contents']) ) {//첨부파일
				$widget['iconfile']		= ' <img src="'.$CI->icon_file_img.'" title="첨부파일" > ';
			}
			if(boardisimage($widget['upload'], $widget['contents']) ) {//첨부파일 > image $widget['upload']  &&
				$widget['iconimage']		= ' <img src="'.$CI->icon_image_img.'" title="첨부파일" align="absmiddle" > ';
			}
			if(isMobilecheck($widget['agent'])) {//agent > mobile ckeck
				$widget['iconmobile']		= ' <img src="'.$CI->icon_mobile_img.'" title="모바일" align="absmiddle" > ';
			}
			$widget['iconhidden'] = ($widget['hidden'] == 1 ) ? ' <img src="'.$CI->icon_hidden_img.'" title="비밀글" > ':'';

			// 메인용 추가 :: 2014-10-21 lwh
			$widget['real_subject']		= $widget['subject'];

			if($widget['display'] == 1 ){//삭제시
				$widget['iconnew']			= '';
				$widget['iconhot']			= '';
				$widget['iconfile']			= '';
				$widget['iconimage']		= '';
				$widget['iconmobile']		= '';
				$widget['iconhidden']		= '';
				$widget['blank']			= ($widget['depth']>0) ? ' <img src="'.$CI->blank_img.'" title="blank" width="'.(($widget['depth']-1)*13).'"  height="1"><img src="'.$CI->re_img.'" title="답변" >':'';//답변
				$commentcnt = ($widget['comment']>0) ? ' <span class="comment">('.number_format($widget['comment']).')</span>':'';
				$widget['subject']		= $widget['blank'].' <span class="hand gray boad_view_btn" viewlink="'.$CI->boardurl->view.$widget['seq'].'"  fileperm_read="'.$CI->manager['fileperm_read'].'"  board_seq="'.$widget['seq'].'"  board_id="'.$CI->widgetboardid.'" ><a>삭제되었습니다 ['.substr($widget['r_date'],0,16).']</a></span>'.$commentcnt;

				$widget['subject']		= getstrcut(strip_tags($widget['subject']), 18);
				$widget['main_subject']	= $widget['blank'].$widget['subject']; 

				$widget['date']			= substr($widget['r_date'],0,16);

				if($widget['replyor'] == 0 && $widget['comment'] == 0) {//삭제후 답변이나  댓글이 없는 경우 삭제가능
					$widget['deletebtn'] = '<span class="btn small  valign-middle"><input type="button" name="boad_delete_btn" board_seq="'.$widget['seq'].'"  board_id="'.$CI->widgetboardid.'" value="삭제" /></span>';
				}
			}else{

				if( $CI->widgetboardid == 'mbqna' ) {
					$widget['subject']		= getstrcut(strip_tags($widget['subject']), 10);
				}else if( $CI->widgetboardid == 'goods_qna' ){
					$widget['subject']		= getstrcut(strip_tags($widget['subject']), 15);
				}else{
					$widget['subject']		= getstrcut(strip_tags($widget['subject']), 28);
				}

				if( $CI->manager['icon_new_day'] > 0 && date("Ymd",strtotime('+'.$CI->manager['icon_new_day'].' day '.substr(str_replace("-","",$widget['r_date']),0,8))) >= date("Ymd") ) {//new
					$widget['iconnew']	= ' <img src="'.$CI->icon_new_img.'" title="new" align="absmiddle" > ';
				}else{
					$widget['iconnew'] ='';
				}

				$widget['date']				= substr($widget['r_date'],0,16);//등록일
				$widget['blank']			= ($widget['depth']>0) ? ' <img src="'.$CI->blank_img.'" title="blank" width="'.(($widget['depth']-1)*53).'"  height="1"><img src="'.$CI->re_img.'" title="답변" >':'';//답변
				$commentcnt = ($widget['comment']>0) ? ' <span class="comment">('.number_format($widget['comment']).')</span>':'';
				if( $CI->widgetboardid == 'store_reservation' ){
					$widget['main_subject']	= $widget['blank'].getstrcut(strip_tags($widget['contents']), 28);
				}else{
					$widget['main_subject']	= $widget['blank'].$widget['subject'];
				}
				$widget['subject'] = $widget['blank'].$widget['category'].''.$widget['subject'].' '.$commentcnt;
			}

			//상품문의/상품후기/대량구매/faq/gs
			$boardmainlistar = array("goods_qna","mbqna","goods_review","bulkorder","faq","gs_seller_notice","gs_seller_qna");
			if( in_array($CI->widgetboardid, $boardmainlistar) ) {
				$addLinkSubject			= ' <span class="hand '.$CI->mainboardclass.' '.$CI->widgetboardid.'_boad_view_btn layer_boad_view_btn" viewlink="'.$CI->boardurl->view.$widget['seq'].'" board_seq="'.$widget['seq'].'"  board_id="'.$CI->widgetboardid.'" >[:SUBJECT:]</span>';
			}elseif( $CI->widgetboardid == 'store_reservation' ) {
				$addLinkSubject		= ' <span class="hand '.$CI->mainboardclass.'  '.$CI->widgetboardid.'_boad_view_btn" viewlink="'.$CI->boardurl->view.$widget['seq'].'" board_seq="'.$widget['seq'].'"  board_id="'.$CI->widgetboardid.'" >[:SUBJECT:]</span>';
			}else{//
				$addLinkSubject		= ' <span class="hand '.$CI->mainboardclass.'  '.$CI->widgetboardid.'_boad_view_btn add_boad_view_btn" viewlink="'.$CI->boardurl->view.$widget['seq'].'" board_seq="'.$widget['seq'].'"  board_id="'.$CI->widgetboardid.'" >[:SUBJECT:]</span>';
			}
			$widget['main_subject']	= str_replace('[:SUBJECT:]', $widget['main_subject'], $addLinkSubject);
			$widget['subject']		= str_replace('[:SUBJECT:]', $widget['subject'], $addLinkSubject);

			if(  $CI->widgetboardid == 'goods_review' ) {
				if($CI->manager['goods_review_type'] == 'INT' && $widget['reviewcategory']){// && sizeof(explode(",",$this->manager['reviewcategory']))>1
					$widget['scorelay'] = getGoodsScore($widget['score_avg'], $CI->manager);
					if(sizeof(explode(",",$widget['reviewcategory']))>1) $widget['score_avg_lay'] = 'score_avg';
				}else{
					$widget['scorelay'] = getGoodsScore($widget['score'], $CI->manager);
				}
				$widget['emoneylay']	=  getBoardEmoneybtn($widget, $CI->manager,'view');
			}

			if(!empty($widget['goods_seq']) && $widget['depth'] == 0 ){
				if( BOARDID == 'bulkorder' ) {
					$widget['goodsInfo']		= getBulkorderGoodsinfo($widget, $widget['goods_seq'], 'write');
					if($widget['goodsInfo'][0]) $widget['goodsInfo'] = $widget['goodsInfo'][0];
					$widget['goodsview']	= getBulkorderGoodsinfo($widget, $widget['goods_seq'], 'view');
				}else{
					$widget['goodsInfo']		= getGoodsinfo($widget, $widget['goods_seq'], 'write');
					if($widget['goodsInfo'][0]) $widget['goodsInfo'] = $widget['goodsInfo'][0];
					$widget['goodsview']	= getGoodsinfo($widget, $widget['goods_seq'], 'view');
				}
			}else{
				$widget['goodsview']	= '';
			}

			// 메인용 날짜 가공 :: 2014-10-21 lwh
			$widget['main_date'] = date('m.d', strtotime($widget['date']));

			$widgetloop[] = $widget;
		}
		$idx--;
		unset($widget);
	}
}

// 게시판게시글평가 리스트노출
function getRecommendviewer($data,$widget=null)
{ 
	$CI =& get_instance();
	$recommendlay = " ";
	if( $CI->manager['auth_recommend_use'] == 'Y' ) {
		if( $CI->manager['recommend_type'] == '3' ) {
			$icon2array = array("recommend1","recommend2","recommend3","recommend4","recommend5");
		}elseif($CI->manager['recommend_type'] == '2'){
			$icon2array = array("recommend","none_rec");
		}elseif($CI->manager['recommend_type'] == '1'){ 
			$icon2array = array("recommend");
		}
		foreach($icon2array as $key => $icontype2) { 
			if($widget ||  $CI->siteType == 'mobile' ) {
				$icontypedepth = ( $key < count($icon2array)-1 )?"/":"";
				$recommendlay .= "<span>".number_format($data[$icontype2])."</span>".$icontypedepth;
			}else{
				$recommendlay .= "<span><img src='".$CI->manager['icon_'.$icontype2.'_src']."' title='".$icontype2."' /> ".number_format($data[$icontype2])."</span><br/>";
			}
		}
	} 
	return  $recommendlay;
}
// END
/* End of file board_helper.php */
/* Location: ./app/helpers/board_helper.php */