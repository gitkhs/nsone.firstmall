<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);

class sns_process extends front_base {

	public function __construct() {

		parent::__construct();
		$this->load->library('validation');
		$this->load->library('session');
		$this->load->library('snssocial');

		$this->load->model('membermodel');
		$this->config_email = config_load('email');
		$this->app_member	= config_load('member');
		
		if($this->app_member['autoApproval']=='Y') {//자동승인인 경우
			$this->app_status = "done";
		}else{
			$this->app_status = "hold";
		}

	}

	//social login url
	public function sociallogin() {
		$this->load->helper('cookiesecure');
		switch($_GET['sns']){
			case 'me2day':
				$this->me2dayaccountck();
			break;
			case 'facebook':
				$this->facebookaccountck();
			break;
			case 'twitter':
				$this->twitterloginck();
			break;
			case 'cyworld':
				$this->cyworldloginck();
			break;
			case 'naver':
				$this->naverloginck();
			break;
		}
	}

	//social login url
	public function snsjoinck() {
		if( !$_GET ){
			parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $_GET);
		}

		$snslogin = false;
		$snswhere_arr = array('session_id'=>$this->session->userdata('session_id'));
		$snsjoindataar = get_data('fm_membersns_join', $snswhere_arr);
		$snsjoindata = $snsjoindataar[0];
		if($snsjoindata['member_seq']){
			$where_arr = array('member_seq'=>$snsjoindata['member_seq']);//, 'status'=>
			$snsdata = get_data('fm_member', $where_arr);
			$snsjoinparams	 = $snsdata[0];
			if($snsjoinparams['status'] == 'done') {
					if(!$_GET['snstype']){
						$_GET['snstype'] = $snsjoinparams['rute'];
					}
					if($_GET['formtype'] == 'myinfojoin'){//마이페이지접근
						$snslogin = $this->sns_login($snsjoinparams,'sns_'.substr($_GET['snstype'],0,1));
					}else{
						$snslogin = $this->sns_login($snsjoinparams,'sns_'.substr($_GET['snstype'],0,1));
					}
			}else{
				pageClose("아직 가입승인되지 않았습니다.\\n가입승인 후 이용해 주세요.");
				exit;
			}
		}
		if($snslogin) {
			if($_GET['formtype'] == 'myinfojoin' && $this->userInfo['member_seq']){//마이페이지접근
				pageRedirect('../mypage/myinfo','','self.close(); opener');
			}elseif($_GET['return_url']){
				pageRedirect($_GET['return_url'],'','self.close(); opener');
			}else{
				pageRedirect('../main/index','','self.close(); opener');
			}
			exit;
		}else{//로그인실패시
			pageClose("접속 정보가 올바르지 않습니다.");
			exit;
		}
	}

	//social myfbrecommend url
	public function snsstreamck() {
		if($_GET['formtype'] == 'myinfojoin' && $this->userInfo['member_seq']){//
			pageRedirect('../mypage/myfbrecommend','','self.close(); opener');
		}else{
			pageRedirect('../main/index','','self.close(); opener');
		}
		exit;
	}

	/**
	@ facebook api start
	------------------------------------------------------------
	**/
		public function facebookaccountck() {
			if($this->arrSns['use_f']) {//사용여부

				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$scope = ($_POST['scope'])?$_POST['scope']:$_GET['scope'];
				$facebooktype = ($_POST['facebooktype'])?$_POST['facebooktype']:$_GET['facebooktype'];

				$callbackurl = urlencode('http://'.$_SERVER['HTTP_HOST'].'/sns_process/facebookloginck?display=popup&mtype='.$mtype.'&mform='.$mform.'&facebooktype='.$facebooktype);
				$login_info = array(
				'scope'			=> $scope,
				'display'		=> ($this->_is_mobile_agent && $this->mobileMode?'touch':'page'),
				'redirect_uri'	=> $callbackurl);
				$loginUrl = $this->snssocial->facebook->getLoginUrl($login_info);
				//$loginurl = 'http://www.facebook.com/dialog/oauth?client_id='.$this->__APP_ID__.'&redirect_uri='.$callbackurl.'&scope=publish_stream,offline_access,user_about_me,email,photo_upload&display='.($this->_is_mobile_agent && $this->mobileMode?'touch':'page').'&state='.$f_start
				if( $loginurl ) {
					$return = array('result'=>true, 'loginurl'=>$loginurl);
				}else{
					$return = array('result'=>false, 'msg'=>'페이스북에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.');
				}
				if($_GET['jsoncallback']) {
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					echo json_encode($return);
				}
				exit;
			}else{
				$return = array('result'=>false, 'msg'=>'잘못된 접근입니다.');
				if($_GET['jsoncallback']) {
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					echo json_encode($return);
				}
				exit;
			}
		}

		public function facebookloginck() {
			if($this->arrSns['use_f']){
				$fbuserprofile = $this->snssocial->facebooklogin();
				if($fbuserprofile){
					$params['rute']				= 'facebook';
					$params['userid']			= ($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];

					if($fbuserprofile['email']) $params['authemail'] = true;
					$params['sns_f']			= $fbuserprofile['id'];
					$params['password']			= '';
					$params['user_name']		= $fbuserprofile['name'];
					if( $fbuserprofile['email'] ){
						$params['email']		= $fbuserprofile['email'];
					}else{
						$params['email']		= ( strstr($fbuserprofile['id'],"@") )?$fbuserprofile['id']:'';
						//($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];
					}
					$params['recommend']	= ($this->session->userdata('recommend') && !$params['recommend'] )?$this->session->userdata('recommend'):$_POST['recommend'];
					$params['sms']				= 'n';
					$params['sex']				= ($fbuserprofile['gender'])?$fbuserprofile['gender']:'none';//enum('male', 'female', 'none')
					$birthday					= @explode("/",$fbuserprofile['birthday']);
					$params['birthday']			= $birthday[2].'-'.$birthday[0].'-'.$birthday[1];
					$params['birth_type']		= 'none';
					$params['status']			= $this->app_status;
					$params['emoney']			= 0;
					$params['login_cnt']		= 0;
					$params['order_cn']	 		= 0;
					$params['order_sum']		= 0;
					$params['mtype']			= ($_POST['mtype'] == 'biz')?true:false;
					$params['regist_date']		= date('Y-m-d H:i:s');

					if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 페북통합하기
						$where_arr = array('sns_f'=>$params['sns_f']);
						$mbdata = get_data('fm_member', $where_arr);
						if(!$mbdata){//회원찾기
							$snsintergration = $this->sns_Integration_direct_ok($params);
							if($snsintergration) {
								$this->sns_login_auth('sns_f');	//로그인세션추가
								$return = array('result'=>true,'retururl'=>'mypage/myinfo');
								echo json_encode($return);
								exit;
							}else{//통합실패
								$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.2");
								echo json_encode($return);
								exit;
							}
						}else{//중복체크
								$this->session->unset_userdata('fbuser');
								$this->session->unset_userdata('accesstoken');
								$this->session->unset_userdata('signedrequest');
								$return = array('result'=>false, 'msg'=>"이미 가입된 페이스북계정 입니다.<br>다른 페이스북 계정으로 가입해 주세요.");
								echo json_encode($return);
								exit;
						}
					}else{
						### QUERY
						$where_arr	= array('sns_f'=>$params['sns_f']);//
						$data		= get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							if( strstr($_SERVER['HTTP_REFERER'],'/member/login')  || strstr($_SERVER['HTTP_REFERER'],'member/register_sns_form?popup=1&formtype=login') || $_POST['facebooktype'] == 'login') {
								//로그인이면 회원가입페이지로 안내하기
								$return = array('result'=>false, 'msg'=>"일치하는 회원정보가 없습니다.<br>회원가입 후 이용해 주세요.");
								echo json_encode($return);
								exit;
							}else{
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									if($this->app_status == "hold"){
										$return		= array('result'=>false,'msg'=>$params['user_name']."님은 아직 가입승인되지 않았습니다.");
										echo json_encode($return);
										exit;
									}else{
										$snslogin	= $this->sns_login($params,'sns_f');
										$return		= array('result'=>true,'retururl'=>'../');
										//$return = array('result'=>true,'retururl'=>'/mypage/');
										echo json_encode($return);
										exit;
									}
								}else{//가입실패시
									$this->session->unset_userdata('fbuser');
									$this->session->unset_userdata('accesstoken');
									$this->session->unset_userdata('signedrequest');
									$return = array('result'=>false, 'msg'=>"이미 가입된 페이스북계정 입니다.<br>다른 페이스북 계정으로 가입해 주세요.");
									echo json_encode($return);
									exit;
								}
							}
						}else{//가입된경우 로그인하기
							if($data[0]['status'] == "hold"){
								$return = array('result'=>false, 'msg'=>$data[0]['user_name']."님은 아직 가입승인되지 않았습니다.");
								echo json_encode($return);
								exit;
							}else{
								$snslogin = $this->sns_login($params,'sns_f');
								if($snslogin) {
									$return = array('result'=>true,'retururl'=>'../');
									//$return = array('result'=>true,'retururl'=>'/mypage/');
									echo json_encode($return);
									exit;
								}else{//로그인실패시
									$return = array('result'=>false, 'msg'=>"탈퇴회원입니다.<br />관리자에게 문의해 주세요.");
									echo json_encode($return);
									exit;
								}
							}
						}
					}
				}else{
					$this->session->unset_userdata('fbuser');
					$this->session->unset_userdata('accesstoken');
					$this->session->unset_userdata('signedrequest');
					$return = array('result'=>false,'type'=>5, 'msg'=>"페이스북에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.");
					echo json_encode($return);
					exit;
				}
			}else{
					$return = array('result'=>false, 'msg'=>"잘못된 접근입니다.4");
					echo json_encode($return);//
					exit;
			}
		}

		public function facebooklogincknone() {//비회원접근시 체크
			if($this->arrSns['use_f']){
				//$this->snssocial->facebooklogin();
				$fbuserprofile = $this->snssocial->facebookuserid();
				if ( !$fbuserprofile ) {
					$this->facebook = new Facebook(array(
					  'appId'  => $this->__APP_ID__,
					  'secret' => $this->__APP_SECRET__,
					  "cookie" => true
					));
					// Get User ID
					$fbuserprofile = $this->facebook->getUser();
					if($fbuserprofile && !$this->session->userdata('fbuser')){
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}else{
						$fbuserprofile = $this->snssocial->facebooklogin();
						if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
							$this->session->set_userdata('fbuser', $fbuserprofile);
						}
					}
				}else{
					if( !$this->session->userdata('fbuser') ) {
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}
				}
			}
		}

		//페이스북로그아웃처리
		public function facebooklogout(){
			$this->session->unset_userdata('user');
			$this->session->unset_userdata('fbuser');
			$this->session->unset_userdata('accesstoken');
			$this->session->unset_userdata('signedrequest');
			$this->session->unset_userdata('nvuser');
			$this->session->unset_userdata('mtype');
			$this->session->unset_userdata('naver_state');
			$this->session->unset_userdata('naver_access_token');
			$this->session->unset_userdata('kkouser');
			$this->session->unset_userdata('dmuser');
			$this->session->unset_userdata('daum_access_token');
			$this->session->unset_userdata('http_host');
			$this->session->unset_userdata('snslogn');
			$_SESSION['user']			= ''; $_SESSION['fbuser']				= '';
			$_SESSION['accesstoken']	= ''; $_SESSION['signedrequest']		= '';
			$_SESSION['nvuser']			= ''; $_SESSION['naver_access_token']	= '';
			$_SESSION['naver_state']	= ''; $_SESSION['mtype']				= '';
			$_SESSION['kkouser']		= ''; 
			$_SESSION['dmuser']			= ''; $_SESSION['daum_access_token']	= '';
			$_SESSION['http_host']		= ''; $_SESSION['snslogn']				= '';
			unset($this->userInfo, $_SESSION['user'], $_SESSION['fbuser'], $_SESSION['naver_state'], $_SESSION['naver_access_token'], $_SESSION['nvuser'], $_SESSION['accesstoken'], $_SESSION['signedrequest'],$_SESSION['kkouser'],$_SESSION['dmuser'],$_SESSION['daum_access_token']); 

			$return = array('result'=>true);
			echo json_encode($return);
			exit;
		}

		/**
		* @ facebook feed 나에게 글남기기
		* @ $params link url
		* @ $params message
		**/
		public function facebookfeed($params){
			 $ret_obj = $this->snssocial->facebook_feed($params);
			 return $ret_obj;
		}

		/**
		* @ facebook 다른곳에 글 남기기
		* @ $params message
		* @ $params imageUrl
		* @ $params name
		* @ $params link
		* @ $params page_id
		**/
		function facebookstreamPublish($params)
		{
			 $ret_obj = $this->snssocial->facebook_streamPublish($params);
			 return $ret_obj;
		}

		/**
		* goods view open graph
		**/
		function goodsview_opengraph($product_url, $type){
			$objectid = $this->snssocial->publishCustomAction($product_url, $type);
			return $objectid;
		}

		/**
		* goods like 모든정보체크하기
		**/
		function facebook_goodsLike(){
			$returnid = $this->snssocial->facebook_goodsLike($_POST['product_url']);
			return $returnid;
		}

		/**
		* goods like session 구하기
		**/
		function facebooklikeck() {
			$this->load->helper('cookie');
			$this->load->model('goodsfblike');
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			if($_GET['firstmallcartid'] &&  $_GET['firstmallcartid']!=$this->session->userdata('session_id')) {
				$this->session->set_userdata('session_id', $_GET['firstmallcartid']);
			}

			$fbuserprofile = $this->snssocial->facebookuserid();
			if ( !$fbuserprofile ) {
				$this->facebook = new Facebook(array(
				  'appId'  => $this->__APP_ID__,
				  'secret' => $this->__APP_SECRET__,
				  "cookie" => true
				));
				// Get User ID
				$fbuserprofile = $this->facebook->getUser();
				if($fbuserprofile && !$this->session->userdata('fbuser')){
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}else{
					$fbuserprofile = $this->snssocial->facebooklogin();
					if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}
				}
			}else{
				if( !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}

			$mode = ($_POST['mode'])?$_POST['mode']:$_GET['mode'];
			$product_url = ($_POST['product_url'])?$_POST['product_url']:$_GET['product_url'];

			$no = ($_POST['no'])?$_POST['no']:$_GET['no'];
			if( $no ) {
				$goodseq = $no;
				if(!strstr($product_url,"&no=")) $product_url = $product_url."&no=".$goodseq;
			}else{
				$goodseq = @end(explode("=",$product_url));
			}

			$this->goodsfblike->set_fblike_goods($mode,$product_url);

			$this->load->model('goodsmodel');
			$product_url = $this->likeurl.'&no='.$goodseq;
			$countreal = $this->snssocial->facebooklikestat($product_url,' like_count, share_count ');
			$this->goodsmodel->goods_like_count($goodseq,$countreal);//like/share count save
			$count = $this->goodsmodel->goods_like_viewer($goods_seq);//상품의 좋아요정보가져오기
			if( strstr($referer['path'], 'order/settle') ) {
				if($count){
					$return = array('result'=>true, 'ftype'=>"settle",'likecount'=>$count['like_count']);
				}else{
					$return = array('result'=>true, 'ftype'=>"settle",'likecount'=>0);
				}
				if( $_GET["jsoncallback"] ) {
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					echo json_encode($return);
				}
				exit;
			}else{
				if($count){
					$return = array('result'=>true, 'ftype'=>"",'likecount'=>$count['like_count']);
				}else{
					$return = array('result'=>true, 'ftype'=>"",'likecount'=>0);
				}
			
				if( $_GET["jsoncallback"] ) {
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					echo json_encode($return);
				}
				exit;
			}
		}

		/* @ facebook 친구불러오기
		* @ 중복체크
		**/
		function facebooksearchfriends(){
			//$fbuserprofile = $this->snssocial->facebooklogin();
			$fbuserprofile = $this->snssocial->facebookuserid();
			if ( !$fbuserprofile ) {
				$this->facebook = new Facebook(array(
				  'appId'  => $this->__APP_ID__,
				  'secret' => $this->__APP_SECRET__,
				  "cookie" => true
				));
				// Get User ID
				$fbuserprofile = $this->facebook->getUser();
				if($fbuserprofile && !$this->session->userdata('fbuser')){
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}else{
					$fbuserprofile = $this->snssocial->facebooklogin();
					if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}
				}
			}else{
				if( !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}

			if(!$fbuserprofile &&$_GET['jsoncallback']  ){
				$return = array('result'=>false, 'publish_stream'=>"publish_stream, publish_actions");
				echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				exit;
			}
			if($fbuserprofile && !$this->userInfo['member_seq']) {
				$params['rute']				= 'facebook';
				$params['userid']				= ($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];
				$params['sns_f']				= $fbuserprofile['id'];
				$params['user_name']		= $fbuserprofile['name'];
				$params['email']				= ($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];
				$params['sex']					= ($fbuserprofile['gender'])?$fbuserprofile['gender']:'none';//enum('male', 'female', 'none')
				$birthday							= @explode("/",$fbuserprofile['birthday']);
				$params['birthday']			= $birthday[2].'-'.$birthday[0].'-'.$birthday[1];
				$this->sns_login($params,'sns_f');
			}

			$searchfriends = $this->snssocial->facebook_searchfriends($_POST);
			$friendstag1 = '';//가입된회원
			$friendstag2 = '';//이미초대한회원
			$friendstag3 = '';
			$friendstags = $friendstage = '';
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			$refererdomain = $referer['host'];

			$name = ($this->arrSns['snstitle'])?urlencode('['.$this->config_basic['shopName'].'] '.$this->arrSns['snstitle']):urlencode('['.$this->config_basic['shopName'].'] ');
			$picture =  ($this->config_system['snslogo'])?'&picture=http://'.$_SERVER['HTTP_HOST'].$this->config_system['snslogo'].'?'.time():'';
			$description = ($this->arrSns['snsDescription'])?urlencode($this->arrSns['snsDescription']):urlencode($this->config_basic['metaTagDescription']);
			$linkurlreal = 'https://www.facebook.com/dialog/feed?display=popup&name='.$name.$picture.'&description='.$description.'&app_id='.$this->__APP_ID__.'&link='.$this->firstmallurl.'/member/fbinvite?fbinvitestr='.$this->userInfo['member_seq'].'&refererdomain='.$refererdomain;

			if($searchfriends['data']){
				$friendstags = '<ul class="suggestSearch">';
					foreach($searchfriends['data'] as $_key=>$friend) {
						$friend['picture'] = $friend['picture']['data']['url'];
						$where_arr = array('sns_f'=>$friend['id'], 'status'=>'done');
						$mbdata = get_data('fm_member', $where_arr);
						$disabled = ($mbdata)?" disabled='disabled' ":"";
						$friendDim = ($mbdata)?" class='friendDim' ":"";

						$invitewhere_arr = array('sns_f'=>$friend['id'],'member_seq'=>$this->userInfo['member_seq']);
						$invitedata = get_data('fm_memberinvite', $invitewhere_arr);
						$friendinvite = ($invitedata)?" style='color:#0c0;text-decoration:underline;' ":"";

						//window.open()
						$linkurl = $linkurlreal.'&to='.$friend['id'].'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/sns_process/recommendconnect?friendid='.$friend['id'];

						if($mbdata){
							$friendstag1 .= '<li '.$friendDim.'>';
							$friendstag1 .= '<label for="'.$friend['id'].'">';
							$friendstag1 .= '<div class="photo"   id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag1 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname "   '.$friendinvite.' >'.$friend['name'].'</span></div>';
							$friendstag1 .= '</label>';
							$friendstag1 .= '</li>';
						}elseif($invitedata){
							$friendstag2 .= '<li '.$friendDim.'>';
							$friendstag2 .= '<label for="'.$friend['id'].'">';
							$friendstag2 .= '<div class="photo" onClick=window.open("'.$linkurl.'","dialogfeed","width=500px,height=300px,statusbar=no,scrollbars=no,toolbar=no"); id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag2 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname" '.$friendinvite.' >'.$friend['name'].'</span></div>';
							$friendstag2 .= '</label>';
							$friendstag2 .= '</li>';
						}else{
							$friendstag3 .= '<li '.$friendDim.'>';
							$friendstag3 .= '<label for="'.$friend['id'].'">';
							$friendstag3 .= '<div class="photo" onClick=window.open("'.$linkurl.'","dialogfeed","width=500px,height=300px,statusbar=no,scrollbars=no,toolbar=no"); id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag3 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname">'.$friend['name'].'</span></div>';
							$friendstag3 .= '</label>';
							$friendstag3 .= '</li>';
						}
					}//endfor
				$friendstage .= '</ul>';
				$friendstag = $friendstags . $friendstag1. $friendstag2. $friendstag3. $friendstage;
			}

			if($_GET['jsoncallback']) {
				if($friendstag){
					$return = array('result'=>true, 'friendstag'=>$friendstag);
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
					if( !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) ) ) {
						$return = array('result'=>false, 'publish_stream'=>"publish_stream, publish_actions");
						echo $_GET["jsoncallback"] ."(".json_encode($return).");";
					}
				}
			}else{
				$return = array('result'=>true, 'friendstag'=>$friendstag);
				echo json_encode($return);
			}
			exit;
		}

		/* @ facebook 친구불러오기
		* @ 중복체크
		**/
		function facebooksearchfriendslay(){
			//$fbuserprofile = $this->snssocial->facebooklogin();
			$fbuserprofile = $this->snssocial->facebookuserid();
			if ( !$fbuserprofile ) {
				$this->facebook = new Facebook(array(
				  'appId'  => $this->__APP_ID__,
				  'secret' => $this->__APP_SECRET__,
				  "cookie" => true
				));
				// Get User ID
				$fbuserprofile = $this->facebook->getUser();
				if($fbuserprofile && !$this->session->userdata('fbuser')){
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}else{
					$fbuserprofile = $this->snssocial->facebooklogin();
					if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}
				}
			}else{
				if( !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}

			$searchfriends = $this->snssocial->facebook_searchfriends($_POST);
			$friendstag1 = '';//가입된회원
			$friendstag2 = '';//이미초대한회원
			$friendstag3 = '';
			$friendstags = $friendstage = '';
			$referer = parse_url($_SERVER['HTTP_REFERER']);
			$refererdomain = $referer['host'];

			$name = ($this->arrSns['snstitle'])?urlencode('['.$this->config_basic['shopName'].'] '.$this->arrSns['snstitle']):urlencode('['.$this->config_basic['shopName'].'] ');
			$picture =  ($this->config_system['snslogo'])?'&picture=http://'.$_SERVER['HTTP_HOST'].$this->config_system['snslogo'].'?'.time():'';
			$description = ($this->arrSns['snsDescription'])?urlencode($this->arrSns['snsDescription']):urlencode($this->config_basic['metaTagDescription']);
			$linkurlreal = 'https://www.facebook.com/dialog/feed?display=popup&name='.$name.$picture.'&description='.$description.'&app_id='.$this->__APP_ID__.'&link='.$this->firstmallurl.'/member/fbinvite?fbinvitestr='.$this->userInfo['member_seq'].'&refererdomain='.$refererdomain;

			if($searchfriends['data']){
				$friendstags = '<ul class="suggestSearch">';
					foreach($searchfriends['data'] as $_key=>$friend) {
						$friend['picture'] = $friend['picture']['data']['url'];
						$where_arr = array('sns_f'=>$friend['id'], 'status'=>'done');
						$mbdata = get_data('fm_member', $where_arr);
						$disabled = ($mbdata)?" disabled='disabled' ":"";
						$friendDim = ($mbdata)?" class='friendDim' ":"";

						$invitewhere_arr = array('sns_f'=>$friend['id'],'member_seq'=>$this->userInfo['member_seq']);
						$invitedata = get_data('fm_memberinvite', $invitewhere_arr);
						$friendinvite = ($invitedata)?" style='color:#0c0;text-decoration:underline;' ":"";

						//window.open()
						$linkurl = $linkurlreal.'&to='.$friend['id'].'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/sns_process/recommendconnect?friendid='.$friend['id'];

						if($mbdata){
							$friendstag1 .= '<li '.$friendDim.'  fid="'.$friend['id'].'" >';
							$friendstag1 .= '<label for="'.$friend['id'].'">';
							$friendstag1 .= '<div class="photo"  id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag1 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname "   '.$friendinvite.' >'.$friend['name'].'</span></div>';
							$friendstag1 .= '</label>';
							$friendstag1 .= '</li>';
						}elseif($invitedata){
							$friendstag2 .= '<li '.$friendDim.'  fid="'.$friend['id'].'" >';
							$friendstag2 .= '<label for="'.$friend['id'].'">';
							$friendstag2 .= '<div class="photo" onClick=window.open("'.$linkurl.'","dialogfeed","width=500px,height=300px,statusbar=no,scrollbars=no,toolbar=no"); id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag2 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname" '.$friendinvite.' >'.$friend['name'].'</span></div>';
							$friendstag2 .= '</label>';
							$friendstag2 .= '</li>';
						}else{
							$friendstag3 .= '<li '.$friendDim.'  fid="'.$friend['id'].'" >';
							$friendstag3 .= '<label for="'.$friend['id'].'">';
							$friendstag3 .= '<div class="photo" onClick=window.open("'.$linkurl.'","dialogfeed","width=500px,height=300px,statusbar=no,scrollbars=no,toolbar=no"); id="'.$friend['id'].'"   fid="'.$friend['id'].'"><img src="'.$friend['picture'].'" alt="" /></div>';
							$friendstag3 .= '<div class="friendInfo"><span class="photoValign"></span><span class="friendname">'.$friend['name'].'</span></div>';
							$friendstag3 .= '</label>';
							$friendstag3 .= '</li>';
						}
					}//endfor
				$friendstage .= '</ul>';
				$friendstag = $friendstags . $friendstag1. $friendstag2. $friendstag3. $friendstage;
			}
			echo 'loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: \'#000000\', speed: 1.5});';
			echo '$("div.inviteFriends div.suggestSearchBox").children().remove();';
			echo '$("div.inviteFriends div.suggestSearchBox").append('.$friendstag.');';
			echo 'loadingStop("body",true);';
			exit;
		}

		/**
		@ facebook feed
		@ 초대하기
		**/
		function facebookfeedsend(){
			$this->load->model('snsfbinvite');

			//$fbuserprofile = $this->snssocial->facebooklogin();
			$fbuserprofile = $this->snssocial->facebookuserid();
			if ( !$fbuserprofile ) {
				$this->facebook = new Facebook(array(
				  'appId'  => $this->__APP_ID__,
				  'secret' => $this->__APP_SECRET__,
				  "cookie" => true
				));
				// Get User ID
				$fbuserprofile = $this->facebook->getUser();
				if($fbuserprofile && !$this->session->userdata('fbuser')){
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}else{
					$fbuserprofile = $this->snssocial->facebooklogin();
					if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
						$this->session->set_userdata('fbuser', $fbuserprofile);
					}
				}
			}else{
				if( !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}

			$memberapproval = config_load('member');
			if(!$fbuserprofile && $_GET['jsoncallback'] ){
				$return = array('result'=>false, 'publish_stream'=>"publish_stream, publish_actions",'login'=>true);
				echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				exit;
			}

			if($_GET['jsoncallback']){
				if($fbuserprofile && !$this->userInfo['member_seq']) {
					$params['rute']				= 'facebook';
					$params['userid']				= ($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];
					$params['sns_f']				= $fbuserprofile['id'];
					$params['user_name']		= $fbuserprofile['name'];
					$params['email']				= ($fbuserprofile['email'])?$fbuserprofile['email']:$fbuserprofile['id'];
					$params['sex']					= ($fbuserprofile['gender'])?$fbuserprofile['gender']:'none';//enum('male', 'female', 'none')
					$birthday							= @explode("/",$fbuserprofile['birthday']);
					$params['birthday']			= $birthday[2].'-'.$birthday[0].'-'.$birthday[1];
					$this->sns_login($params,'sns_f');
				}
				$toStr				= @explode(",",$_GET['toStr']);
				$_POST['message']		= $_GET['message'];
			}else{
				$toStr				= @explode(",",$_POST['toStr']);
				$_POST['message']		= $_POST['message'];
			}
			$uptostr = '';
			if(is_array($toStr)) {
				foreach($toStr as $_key => $_val) {
					$feeddata['friendid']		=$_val;
					$feeddata['message']		= $_POST['message'];
					$feeddata['name']			= '['.$this->config_basic['shopName'].'] '.$this->config_basic['shopTitleTag'];
					$feeddata['description']	= $this->config_basic['metaTagDescription'];
					$feeddata['link']				= $this->firstmallurl.'/member/fbinvite?fbinvitestr='. $this->userInfo['member_seq'];
					$feedsend = $this->snssocial->facebook_friendfeed($feeddata);
					if($feedsend['error']) {
						$return = array('result'=>false, 'msg'=>$feedsend['message']);
						if($_GET['jsoncallback']) {
							$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
							//if( !array_key_exists('publish_stream', $fbpermissions['data'][0]) && !$invitedcnt ) {
							if( !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) ) && !$invitedcnt ) {
								$return = array('result'=>false, 'publish_stream'=>"publish_stream, publish_actions","fedd"=>"error");
								echo $_GET["jsoncallback"] ."(".json_encode($return).");";
							}else{
								echo $_GET["jsoncallback"] ."(".json_encode($return).");";
							}
						}else{
							echo json_encode($return);
						}
						exit;
					}elseif($feedsend){
						$invitedcnt++;
						$sc['sns_f'] = $feeddata['friendid'];
						$sc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
						$sc['select'] = ' seq ';
						$inviteck = $this->snsfbinvite->get_data_numrow($sc);
						if(!$inviteck){//초대여부 -> 적립금 지급
							$totalinvitesc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
							$totalinvitesc['select']		= ' seq ';
							$totalinviteck = $this->snsfbinvite->get_data_numrow($totalinvitesc);
							if(($totalinviteck+1) <= $memberapproval['invitemaxcount']) {//최대 초대건수
								if( (($totalinviteck+1)%$memberapproval['invitecount']) == 0 ){
									if($memberapproval['emoneyInvitedCnt'] > 0 ) {
										$emoney['type']			= 'invite_whenever';//초대할때마다
										$emoney['emoney']		= $memberapproval['emoneyInvitedCnt'];//초대하는사람에게
										$emoney['gb']				= 'plus';
										$emoney['memo']		= $memberapproval['invitecount'].'명 초대시 적립금';
										$emoney['limit_date']	= get_emoney_limitdate('invite');
										$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
									}

									if($memberapproval['pointInvitedCnt'] > 0 ) {
										$point['type']			= 'invite_whenever';//초대할때마다
										$point['point']			= $memberapproval['pointInvitedCnt'];//초대하는사람에게
										$point['gb']				= 'plus';
										$point['memo']			= $memberapproval['invitecount'].'명 초대시 포인트';
										$point['limit_date']	= get_point_limitdate('invite');
										$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
									}
								}
							}
							$insparams['member_seq']	= $this->userInfo['member_seq'];
							$insparams['sns_f']				= $feeddata['friendid'];
							$insparams['emoney']			= $memberapproval['emoneyInvitedCnt'];
							$insparams['r_date']			= date('Y-m-d H:i:s');
							$this->snsfbinvite->snsinvite_write($insparams);
						}//endif
					}//endif
				}//endfor
			}//endif;

			if($invitedcnt){
				$totalinvitesc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
				$totalinvitesc['select']		= ' seq ';
				$totalinviteck = $this->snsfbinvite->get_data_numrow($totalinvitesc);
				$return = array('result'=>true, 'msg'=>number_format($invitedcnt).'명에게 [초대하기]를 성공하였습니다!','totalinviteck'=>number_format($totalinviteck),'invitemaxcount'=>number_format($memberapproval['invitemaxcount']));
			}else{
				$return = array('result'=>false, 'msg'=>number_format(count($toStr)).'명에게 [초대하기]를 실패하였습니다!');
			}
			if($_GET['jsoncallback']){
				$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
				//if( !array_key_exists('publish_stream', $fbpermissions['data'][0]) && !$invitedcnt ) {
				if( !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) ) && !$invitedcnt ) {
					$return = array('result'=>false, 'publish_stream'=>"publish_stream, publish_actions");
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}else{
					echo $_GET["jsoncallback"] ."(".json_encode($return).");";
				}
			}else{
				echo json_encode($return);
			}
			exit;
		}

		/* @ facebook 초대하기 후 창닫기 개별시
		* @ recommendconnect
		**/
		function recommendconnect(){
			$this->load->model('snsfbinvite');
			login_check();
			if($_GET['post_id']) {

				$memberapproval = config_load('member');

				$sc['sns_f'] = $_GET['friendid'];
				$sc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
				$sc['select'] = ' seq ';
				$inviteck = $this->snsfbinvite->get_data_numrow($sc);
				if(!$inviteck){//초대여부 -> 적립금 지급
					$totalinvitesc['whereis']	= ' and member_seq = \''.$this->userInfo['member_seq'].'\' ';
					$totalinvitesc['select']		= ' seq ';
					$totalinviteck = $this->snsfbinvite->get_data_numrow($totalinvitesc);
					if(($totalinviteck+1) <= $memberapproval['invitemaxcount']) {//최대 초대건수
						if( (($totalinviteck+1)%$memberapproval['invitecount']) == 0 ){

							if($memberapproval['emoneyInvitedCnt'] > 0 ) {
								$emoney['type']			= 'invite_whenever';//초대할때마다
								$emoney['emoney']		= $memberapproval['emoneyInvitedCnt'];//초대하는사람에게
								$emoney['gb']				= 'plus';
								$emoney['memo']		= $memberapproval['invitecount'].'명 초대시 적립금';
								$emoney['limit_date']	= get_emoney_limitdate('invite');
								$this->membermodel->emoney_insert($emoney, $this->userInfo['member_seq']);
							}

							if($memberapproval['pointInvitedCnt'] > 0 ) {
								$point['type']			= 'invite_whenever';//초대할때마다
								$point['point']			= $memberapproval['pointInvitedCnt'];//초대하는사람에게
								$point['gb']				= 'plus';
								$point['memo']			= $memberapproval['invitecount'].'명 초대시 포인트';
								$point['limit_date']	= get_point_limitdate('invite');
								$this->membermodel->point_insert($point, $this->userInfo['member_seq']);
							}

						}
					}
					$insparams['member_seq']	= $this->userInfo['member_seq'];
					$insparams['sns_f']				= $_GET['friendid'];
					$insparams['post_id']				= $_GET['post_id'];
					$insparams['emoney']			= $memberapproval['emoneyInvitedCnt'];
					$insparams['r_date']			= date('Y-m-d H:i:s');
					$this->snsfbinvite->snsinvite_write($insparams);

					//초대한자의 초대건수 증가 @2013-06-19
					$this->membermodel->member_invite_cnt($this->userInfo['member_seq']);

				}//endif
				if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
					//echo js("opener.location.reload()");//'&refererdomain='.$refererdomain.
					pageReload('초대하기에 성공하였습니다!','opener');
					pageClose();
				}else{
					pageClose('초대하기에 성공하였습니다!');
				}
			}else{
				if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ) {
					//echo js("opener.location.reload()");
					pageReload('초대하기에 실패하였습니다!','opener');
					pageClose();
				}else{
					pageClose();
				}
			}
		}
	/**
	@ facebook api end
	------------------------------------------------------------
	**/


	/**
	@ twitter api start
	------------------------------------------------------------
	**/
		//twitter의 로그인체크 (본래창)
		public function twitterloginck() {
			if($this->arrSns['use_t']) {//twitter 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$facebooktype = ($_POST['facebooktype'])?$_POST['facebooktype']:$_GET['facebooktype'];
				$loginurl = $this->snssocial->twitterloginurl($mtype,$mform, $facebooktype);
				if($loginurl) {
					$return = array('result'=>true, 'loginurl'=>$loginurl);
					echo json_encode($return);
					exit;
				}else{
					$return = array('result'=>false, 'msg'=>'트위터에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.');
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>'잘못된 접근입니다.');
				echo json_encode($return);
				exit;
			}
		}


		//twitter 쇼핑몰회원가입 (새창)
		public function twitterjoin() {
			if($this->arrSns['use_t']) {//twitter 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$facebooktype = ($_POST['facebooktype'])?$_POST['facebooktype']:$_GET['facebooktype'];
				$twuserprofile = $this->snssocial->twitteraccount($_GET['oauth_verifier'], $mtype, 'join', $facebooktype);

				if($_GET['denied']){
					pageClose("트위터에서 회원정보를 가져오지 못하였습니다.\\n관리자에게 문의해 주세요.");
					exit;
				}elseif( !$this->session->userdata('oauth_token') && !$this->session->userdata('oauth_token_secret') ) {
					pageClose("잘못된 접근입니다.");
					exit;
				}else{
					if( $twuserprofile['id'] ) {
						$params['rute']				= 'twitter';
						$params['userid']				= $twuserprofile['screen_name'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'tw_'.$params['userid'];

						if( strstr($twuserprofile['id'],"@") ) $params['authemail'] = true;
						$params['sns_t']				= $twuserprofile['id'];
						$params['password']		= '';
						$params['user_name']		= $twuserprofile['screen_name'];
						$params['email']				= ( strstr($twuserprofile['id'],"@") )?$twuserprofile['id']:'';
						$params['sms']				= 'n';
						$params['sex']					= 'none';
						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']				= ($mtype == 'biz')?true:false;
						$params['regist_date']	= date('Y-m-d H:i:s');

						if($facebooktype == 'mbconnect_direct') {//로그인된 상태에서 twitter통합하기
							$where_arr	= array('sns_t'=>$params['sns_t'], 'status'=>'done');
							$mbdata		= get_data('fm_member', $where_arr);
							if(!$mbdata){//회원찾기
								$snsintergration = $this->sns_Integration_direct_ok($params);
								if($snsintergration) {
									$this->sns_login_auth('sns_t');	//로그인세션추가
									//가입완료후 본래창 자동로그인처리하기
									if( $this->session->userdata('snsreferer') != $_SERVER['HTTP_HOST'] && $this->session->userdata('snsreferer')){
										pageRedirect('http://'.$this->session->userdata('snsreferer').'/sns_process/snsjoinck','');
										exit;
									}else{
										pageRedirect('../mypage/myinfo','','self.close(); opener');
										exit;
									}

								}else{//통합실패
									pageClose("잘못된 접근입니다.2");
									exit;
								}
							}else{//중복체크
								$this->session->unset_userdata('twuser');
								$this->session->unset_userdata('twitter_oauth_token');
								$this->session->unset_userdata('twitter_oauth_token_secret');
								pageClose("이미 가입된 트위터계정 입니다.\\n다른 트위터 계정으로 가입해 주세요.");
								exit;
							}
						}else{
							### QUERY
							$where_arr = array('sns_t'=>$params['sns_t']);//
							$data = get_data('fm_member', $where_arr);
							if(!$data) {//정보가 없을 경우 가입후 로그인하기
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									if($this->app_status == "hold"){
										$result = false;
										$msg	= $params['user_name']."님은 아직 가입승인되지 않았습니다.";
									}else{
										$snslogin = $this->sns_login($params,'sns_t');
										//가입완료후 본래창 자동로그인처리하기
										if( $this->session->userdata('snsreferer') != $_SERVER['HTTP_HOST'] && $this->session->userdata('snsreferer')){
											pageRedirect('http://'.$this->session->userdata('snsreferer').'/sns_process/snsjoinck','');
											exit;
										}else{
											pageRedirect('../main/index','','self.close(); opener');
											exit;
										}
									}
								}else{//가입실패시
									$this->session->unset_userdata('twuser');
									$this->session->unset_userdata('twitter_oauth_token');
									$this->session->unset_userdata('twitter_oauth_token_secret');
									pageClose("이미 가입된 트위터계정 입니다.\\n다른 트위터 계정으로 가입해 주세요.");
									exit;
								}
							}else{//이미가입된경우 로그인하기
								if($data[0]['status'] == "hold"){
									pageClose($data[0]['user_name']."님은 아직 가입승인되지 않았습니다.");
									exit;
								}else{
									$snslogin = $this->sns_login($params,'sns_t');
									if($snslogin) {
										//가입완료후 본래창 자동로그인처리하기
										if( $this->session->userdata('snsreferer') != $_SERVER['HTTP_HOST'] && $this->session->userdata('snsreferer')){
											pageRedirect('http://'.$this->session->userdata('snsreferer').'/sns_process/snsjoinck','');
											exit;
										}else{
											pageRedirect('../main/index','','self.close(); opener');
											exit;
										}
									}else{//로그인실패시
										pageClose("탈퇴회원입니다.\\n관리자에게 문의해 주세요.");
										exit;
									}
								}
							}
						}
					}else{
						pageClose("잘못된 접근입니다.4 ".implode("\\n->",$twuserprofile->errors[0]->message));
						exit;
					}
				}
			}else{
				pageClose("잘못된 접근입니다.5");
				exit;
			}
		}

		//twitter 쇼핑몰로그인하기 (새창)
		public function twitterlogin() {
			if($this->arrSns['use_t']) {//twitter 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$twuserprofile = $this->snssocial->twitteraccount($_GET['oauth_verifier'], $mtype, 'login','');
				if($_GET['denied']){
					pageClose("트위터에서 회원정보를 가져오지 못하였습니다.\\n관리자에게 문의해 주세요.");
					exit;
				}elseif( !$this->session->userdata('oauth_token') && !$this->session->userdata('oauth_token_secret') ) {
					pageClose("잘못된 접근입니다.");
					exit;
				}else{
					if( $twuserprofile['id']) {
						$params['rute']				= 'twitter';
						$params['userid']			= $twuserprofile['screen_name'];

						$this->db->where('userid', $params['userid']);
						$query		= $this->db->get("fm_member");
						$mem_chk	= $query->result_array();
						if($mem_chk) $params['userid'] = 'tw_'.$params['userid'];

						$params['sns_t']				= $twuserprofile['id'];

						### QUERY
						$where_arr	= array('sns_t'=>$params['sns_t']);//
						$data		= get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							pageClose("일치하는 회원정보가 없습니다.\\n회원가입 후 이용해 주세요.");
							exit;
						}else{//가입된경우 로그인하기
							if($data[0]['status'] == "hold"){
								pageClose($data[0]['user_name']."님은 아직 가입승인되지 않았습니다.");
								exit;
							}else{
								$snslogin = $this->sns_login($params,'sns_t');
								if($snslogin) {
									//가입완료후 본래창 자동로그인처리하기
									if( $this->session->userdata('snsreferer') != $_SERVER['HTTP_HOST'] && $this->session->userdata('snsreferer')){
										pageRedirect('http://'.$this->session->userdata('snsreferer').'/sns_process/snsjoinck','');
										exit;
									}else{
										pageRedirect('../main/index','','self.close(); opener');
										exit;
									}
									exit;
								}else{//로그인실패시
									pageClose("탈퇴회원입니다.<br />관리자에게 문의해 주세요.");
									exit;
								}
							}
						}
					}else{
						pageClose("잘못된 접근입니다.4 ".implode("\\n->",$twuserprofile));
						exit;
					}
				}
			}else{
				pageClose("잘못된 접근입니다.5");
				exit;
			}
		}
	/**
	@ twitter api end
	------------------------------------------------------------
	**/


	/**
	@ me2day api start
	------------------------------------------------------------
	**/
		//me2day 로그인체크 (본래창)
		public function me2dayloginck() {
			if($this->arrSns['use_m']) {//me2day 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$loginurl = $this->snssocial->me2dayloginurl($mtype,$mform);
				if($loginurl) {
					$return = array('result'=>true, 'loginurl'=>$loginurl);
					echo json_encode($return);
					exit;
				}else{
					$return = array('result'=>false, 'msg'=>'미투데이에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.');
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>'잘못된 접근입니다.');
				echo json_encode($return);
				exit;
			}
		}


		//me2day 로그인체크 callbackurl (새창1)
		public function me2dayaccountck() {
			if($this->arrSns['use_m']) {//me2day 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$m2userprofile = $this->snssocial->me2dayaccountck($mtype, 'join');
				if($_GET['result'] ==  false && $_GET['user_id']){
					pageClose("미투데이에서 회원정보를 가져오지 못하였습니다.\\n관리자에게 문의해 주세요.");
					exit;
				}else{
					if( $m2userprofile['id']) {
						echo js(" self.close(); opener.me2dayjoginlogin(); ");
						exit;
					}else{
						pageClose("잘못된 접근입니다2.");
						exit;
					}
				}
			}else{
				pageClose("잘못된 접근입니다1.");
				exit;
			}
		}

		//me2day 쇼핑몰회원가입 (새창)
		public function me2dayjoin() {
			if($this->arrSns['use_m']) {//me2day 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$m2userprofile = $this->snssocial->me2dayaccount($mtype, 'join');
				if(!$this->session->userdata('m2user') ) {
					$return = array('result'=>false,'msg'=>"잘못된 접근입니다.");
					echo json_encode($return);
					exit;
				}else{
					if( $m2userprofile['id']) {
						$params['rute']				= 'me2day';
						$params['userid']				= $m2userprofile['id'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'm2_'.$params['userid'];

						$params['sns_m']			= $m2userprofile['user_key'];
						$params['password']		= '';
						$params['user_name']		=  $m2userprofile['realname'];
						$params['email']				= $m2userprofile['email'];
						$params['cellphone']		= $m2userprofile['cellphone'];
						$m2userprofile['birthday'] = str_replace("년","-",$m2userprofile['birthday']);
						$m2userprofile['birthday'] = str_replace("월","-",$m2userprofile['birthday']);
						$m2userprofile['birthday'] = str_replace("일생","",$m2userprofile['birthday']);
						$params['birthday']			= $m2userprofile['birthday'];
						$params['sms']				= 'n';

						if($m2userprofile['sex'])
							$params['sex']					= ($m2userprofile['sex']=='Woman')?'female':'male';//Man

						$params['birth_type']		= 'none';
						$params['status']				= 'done';
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']				= ($mtype == 'biz')?true:false;
						$params['regist_date']	= date('Y-m-d H:i:s');

						if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 me2day통합하기
							$where_arr = array('sns_m'=>$params['sns_m'], 'status'=>'done');
							$mbdata = get_data('fm_member', $where_arr);
							if(!$mbdata){//회원찾기
								$snsintergration = $this->sns_Integration_direct_ok($params);
								if($snsintergration) {
									$this->sns_login_auth('sns_m');	//로그인세션추가
									$return = array('result'=>true,'retururl'=>'/mypage/myinfo');
									echo json_encode($return);
									exit;
								}else{//통합실패
									$return = array('result'=>false,'msg'=>"잘못된 접근입니다2.");
									echo json_encode($return);
									exit;
								}
							}else{//중복체크
								$this->session->unset_userdata('twuser');
								$this->session->unset_userdata('me2day_oauth_token');
								$this->session->unset_userdata('me2day_oauth_token_secret');
								$return = array('result'=>false,'msg'=>"이미 가입된 미투데이 계정 입니다.<br />다른 미투데이 계정으로 가입해 주세요.");
								echo json_encode($return);
								exit;
							}
						}else{
							### QUERY
							$where_arr = array('sns_m'=>$params['sns_m'], 'status'=>'done');//
							$data = get_data('fm_member', $where_arr);
							if(!$data) {//정보가 없을 경우 가입후 로그인하기
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									$snslogin = $this->sns_login($params,'sns_m');
									if($snslogin) {
										$return = array('result'=>true,'retururl'=>'../');
										echo json_encode($return);
										exit;
									}else{//로그인실패시
										$return = array('result'=>false,'msg'=>"탈퇴회원입니다.<br />관리자에게 문의해 주세요.");
										echo json_encode($return);
										exit;
									}
									exit;
								}else{//가입실패시
									$this->session->unset_userdata('twuser');
									$this->session->unset_userdata('me2day_oauth_token');
									$this->session->unset_userdata('me2day_oauth_token_secret');

									$return = array('result'=>false,'msg'=>"이미 가입된 미투데이 계정 입니다.<br />다른 미투데이 계정으로 가입해 주세요.");
									echo json_encode($return);
									exit;
								}
							}else{//이미가입된경우 로그인하기
								$snslogin = $this->sns_login($params,'sns_m');
								if($snslogin) {
									$return = array('result'=>true,'retururl'=>'../');
									//$return = array('result'=>true,'retururl'=>'/mypage/');
									echo json_encode($return);
									exit;
								}else{//로그인실패시
									$return = array('result'=>false,'msg'=>"탈퇴회원입니다.<br />관리자에게 문의해 주세요.");
									echo json_encode($return);
									exit;
								}
							}
						}
					}else{
						$return = array('result'=>false,'msg'=>"잘못된 접근입니다.4 ");
						echo json_encode($return);
						exit;
					}
				}
			}else{
				$return = array('result'=>false,'msg'=>"잘못된 접근입니다.4 ");
				echo json_encode($return);
				exit;
			}
		}

		//me2day 쇼핑몰로그인하기 (새창)
		public function me2daylogin() {
			if($this->arrSns['use_m']) {//me2day 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$m2userprofile = $this->snssocial->me2dayaccount($mtype, 'join');
				if(!$this->session->userdata('m2user') ) {
					$return = array('result'=>false,'msg'=>"잘못된 접근입니다.5");
					echo json_encode($return);
					exit;
				}elseif($_GET['result'] ==  false && $_GET['user_id']) {
					pageClose("미투데이에서 회원정보를 가져오지 못하였습니다.");
					exit;
				}else{
					if( $m2userprofile['id']) {
						$params['rute']				= 'me2day';
						$params['userid']				= $m2userprofile['id'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'm2_'.$params['userid'];

						$params['sns_m']				= $m2userprofile['user_key'];
						### QUERY
						$where_arr = array('sns_m'=>$params['sns_m'], 'status'=>'done');//
						$data = get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$return = array('result'=>false,'msg'=>"일치하는 회원정보가 없습니다.<br />회원가입 후 이용해 주세요");
							echo json_encode($return);
							exit;
						}else{//가입된경우 로그인하기
							$snslogin = $this->sns_login($params,'sns_m');
							if($snslogin) {
								$return = array('result'=>true,'retururl'=>'../');
								echo json_encode($return);
								exit;
							}else{//로그인실패시
								$return = array('result'=>false,'msg'=>"탈퇴회원입니다.<br />관리자에게 문의해 주세요.");
								echo json_encode($return);
								exit;
							}
						}
					}else{
						$return = array('result'=>false,'msg'=>"잘못된 접근입니다.2");
						echo json_encode($return);
						exit;
					}
				}
			}
		}
	/**
	@ me2day api end
	------------------------------------------------------------
	**/

	/**
	@ cyworld api start
	------------------------------------------------------------
	**/
		//cyworld 로그인체크 (본래창)
		public function cyworldloginck() {
			if($this->arrSns['use_c']) {//cyworld 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$loginurl = $this->snssocial->cyworldloginurl($mtype,$mform);
				if($loginurl) {
					$result		= true;
					$loginurl	= $loginurl;
				}else{
					$result = false;
					$msg	= '네이트에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.';
				}
			}else{
				$result = false;
				$msg	= "잘못된 접근입니다.";
			}
			$return = array("result"=>$result,"msg"=>$msg,"loginurl"=>$loginurl);
			echo json_encode($return);
			exit;
		}

		//cyworld 로그인체크 callbackurl (새창)
		public function cyworlduserck() {
			if($this->arrSns['use_c']) {//cyworld 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$cyworldaccesstoken = $this->snssocial->cyworldaccesstoken($mtype, 'join');
				if( !$this->session->userdata('cyworld_request_token_secret') ) {
					pageClose("잘못된 접근입니다1.");
					exit;
				}elseif (!isset($_GET['oauth_token']) && !isset($_GET['oauth_verifier'])) {
					pageClose("잘못된 접근입니다2.");
					exit;
				}else{
					if( $cyworldaccesstoken) {
						echo js(" self.close(); opener.cyworldjoginlogin(); ");
						exit;
					}else{
						pageClose("잘못된 접근입니다3.");
						exit;
					}
				}
			}else{
				pageClose("잘못된 접근입니다4.");
				exit;
			}
		}

		//cyworld 쇼핑몰회원가입 (새창)
		public function cyworldjoin() {
			if($this->arrSns['use_c']) {//cyworld 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$cyworlduserprofile = $this->snssocial->cyworldaccount( $mtype, 'join');
				if( !$this->session->userdata('cyworld_request_token_secret') ||  !$this->session->userdata('cyuser') ) {
					$result = false;
					$msg	= "잘못된 접근입니다.1";
				}else{
					if( $cyworlduserprofile['id']) {
						$params['rute']				= 'cyworld';
						$params['email']				= ( strstr($cyworlduserprofile['id'],"@") )?$cyworlduserprofile['id']:'';
						$params['userid']				= $cyworlduserprofile['id'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'cy_'.$params['userid'];

						$params['sns_c']				= $cyworlduserprofile['id'];
						$params['password']		= '';
						$params['user_name']		= $cyworlduserprofile['name'];//
						$params['email']				= '';
						$params['sms']				= 'n';

						if($cyworlduserprofile['sex'])
							$params['sex']					= ($cyworlduserprofile['sex']=='2')?'female':'male';//Man

						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']			= ($mtype == 'biz')?true:false;
						$params['regist_date']		= date('Y-m-d H:i:s');

						if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 요즘통합하기
							$where_arr = array('sns_c'=>$params['sns_c']);
							$mbdata = get_data('fm_member', $where_arr);
							if(!$mbdata){//회원찾기
								$snsintergration = $this->sns_Integration_direct_ok($params);
								if($snsintergration) {
									$this->sns_login_auth('sns_c');	//로그인세션추가
									$result		= true;
									$retururl	= '/mypage/myinfo';
								}else{//통합실패
									$result = false;
									$msg	= "잘못된 접근입니다2.";
								}
							}else{//중복체크
								$this->session->unset_userdata('cyuser');
								$this->session->unset_userdata('cyworld_request_token_secret');
								$result = false;
								$msg	= "이미 가입된 싸이월드 계정 입니다.<br />다른 싸이월드 계정으로 가입해 주세요.";
							}
						}else{
							$where_arr	= array('sns_c'=>$params['sns_c']);//
							$data		= get_data('fm_member', $where_arr);
							if(!$data) {//정보가 없을 경우 가입후 로그인하기
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									if($this->app_status == "hold"){
										$result = false;
										$msg	= $params['user_name']."님은 아직 가입승인되지 않았습니다.";
									}else{
										$snslogin = $this->sns_login($params,'sns_c');
										if($snslogin) {
											$result		= true;
											$retururl	= '../';
										}else{//로그인실패시
											$result = false;
											$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
										}
									}
								}else{//가입실패시
									$this->session->unset_userdata('cyuser');
									$this->session->unset_userdata('request_token');
									$result = false;
									$msg	= "이미 가입했거나 탈퇴회원입니다.";
								}
							}else{//이미가입된경우 로그인하기
								if($data[0]['status'] == 'hold'){
									$result = false;
									$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
								}else{
									$snslogin = $this->sns_login($params,'sns_c');
									if($snslogin) {
										$result		= true;
										$retururl	= '../';
									}else{//로그인실패시
										$result = false;
										$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
									}
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다.";
					}
				}
			}else{
				$result = false;
				$msg	= "싸이월드 로그인 연동 사용 여부를 확인해 주세요.";
			}

			$return = array("result"=>$result,"msg"=>$msg,"retururl"=>$retururl);

			echo json_encode($return);
			exit;
		}

		//cyworld 쇼핑몰로그인하기 (새창)
		public function cyworldlogin() {
			if($this->arrSns['use_c']) {//cyworld 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$cyworlduserprofile = $this->snssocial->cyworldaccount( $mtype, 'login');
				if( !$this->session->userdata('cyworld_request_token_secret') ||  !$this->session->userdata('cyuser') ) {
					$result = false;
					$msg	= "잘못된 접근입니다.1";
				}else{
					if( $cyworlduserprofile['id']) {
						$params['rute']				= 'cyworld';
						$params['email']			= ( strstr($cyworlduserprofile['id'],"@") )?$cyworlduserprofile['id']:'';
						$params['userid']			= $cyworlduserprofile['id'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'cy_'.$params['userid'];

						$params['sns_c']				= $cyworlduserprofile['id'];
						$params['password']			= '';
						$params['user_name']		= $cyworlduserprofile['name'];//
						$params['email']				= '';
						$params['sms']				= 'n';

						if($cyworlduserprofile['sex'])
							$params['sex']					= ($cyworlduserprofile['sex']=='2')?'female':'male';//Man

						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']			= ($mtype == 'biz')?true:false;
						$params['regist_date']		= date('Y-m-d H:i:s');
						$where_arr					= array('sns_c'=>$params['sns_c']);//
						$data						= get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$result = false;
							$msg	= "일치하는 회원정보가 없습니다.<br />회원가입 후 이용해 주세요";
						}else{//가입된경우 로그인하기
							if($data[0]['status'] == 'hold'){
								$result = false;
								$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
							}else{
								$snslogin = $this->sns_login($params,'sns_c');
								if($snslogin) {
									$result		= true;
									$retururl	= '../';
								}else{//로그인실패시
									$result = false;
									$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다.";
					}
				}
			}else{
				$result = false;
				$msg	= "싸이월드 로그인 연동 사용 여부를 확인해 주세요.";
			}
			$return = array("result"=>$result,"msg"=>$msg,"retururl"=>$retururl);

			echo json_encode($return);
			exit;
		}
	/**
	@ cyworld api end
	------------------------------------------------------------
	**/

	/**
	@ naver api start
	------------------------------------------------------------
	**/
		//naver 로그인체크 (본래창) //login callback
		public function naverloginck() {
			if($this->arrSns['use_n']) {//naver 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$loginurl = $this->snssocial->naverloginurl($mtype,$mform);

				if($loginurl) {
					if($_POST['m'] == "myinfo") $loginurl = urlencode($loginurl);
					$return = array('result'=>true, 'loginurl'=>$loginurl);
					echo json_encode($return);
					exit;
				}else{
					$return = array('result'=>false, 'msg'=>'네이버에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.');
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>'잘못된 접근입니다.');
				echo json_encode($return);
				exit;
			}
		}

		//naver 로그인체크 callbackurl (새창)
		public function naveruserck() {
			if($this->arrSns['use_n']) {//naver 사용여부

				$sess_mtype = ($this->session->userdata('mtype'))? $this->session->userdata('mtype'):$_SESSION['mtype'];
				$sess_http_host = ($this->session->userdata('http_host'))? $this->session->userdata('http_host'):$_SESSION['http_host'];
				$sess_naver_state = ($this->session->userdata('naver_state'))? $this->session->userdata('naver_state'):$_SESSION['naver_state'];
				$sess_naver_access_token = (trim($this->session->userdata('naver_access_token')))? $this->session->userdata('naver_access_token'):$_SESSION['naver_access_token'];

				## callback url host 와 실제 접근한 host 가 서로 다를 경우 실제 host 로 리다이렉트
				if(!$_GET['ok'] && $_SERVER['HTTP_HOST'] != $sess_http_host){
					
					if($sess_http_host){
						$pram = array();
						foreach($_GET as $k=>$v){
							$pram[] = $k."=".$v;
						}
						$pram_tmp = "&".implode("&",$pram);
						$re_url = $sess_http_host."/sns_process/naveruserck?ok=1".$pram_tmp;
						echo js("location.href='http://".$re_url."'");
						exit;
					}else{
						pageClose("정상적인 접근이 아닙니다.");
						exit;
					}

				}else{
					//$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
					$mtype			= $sess_mtype;
					$naveraccesstoken = $this->snssocial->naveraccesstoken($mtype, 'join');
					$err_msg = $this->getnavererrormsg($naveraccesstoken['error']);
					if($err_msg){
						pageClose($err_msg);
						exit;
					}else{
						if( !$sess_naveraccesstoken['error']) {
							echo js(" self.close(); opener.naverjoinlogin(); ");
							exit;
						}else{
							pageClose(getnavererrormsg($naveraccesstoken['error']));
							exit;
						}
					}
				}
			}else{
				pageClose("관리자 > 네이버 로그인 사용여부를 확인해 주세요.");
				exit;
			}
		}

		public function getnavererrormsg($cd){

			$nv_login_error_msg = array();
			$nv_login_error_msg['session_error']			= "인증받은 세션이 종료되었습니다.\\n새로고침 후 다시 시도해 주세요";
			//$nv_login_error_msg['invalid_request']			= "파라미터 또는 요청문이 정상적이지 않습니다.\n시스템관리자에게 문의해 주세요.";
			$nv_login_error_msg['invalid_request']			= "요청문이 정상적이지 않습니다.\\nCallback URL, Client ID, Client Key 값을 다시 한번 확인해 주세요.";
			$nv_login_error_msg['unauthorized_client']		= "인증받지 않은 '인증허가코드' 입니다.\\n시스템관리자에게 문의해 주세요.";
			$nv_login_error_msg['unsupported_response_type'] = "정의되어있지 않은 response type 입니다.\\n시스템관리자에게 문의해 주세요.";
			$nv_login_error_msg['server_error']				= "네이버 인증서버 오류입니다.\\n시스템관리자에게 문의해 주세요.";

			return $nv_login_error_msg[$cd];
		}

		//naver 쇼핑몰회원가입 (새창)
		public function naverjoin() {

			$sess_mtype		= ($this->session->userdata('mtype'))? $this->session->userdata('mtype'):$_SESSION['mtype'];
			$sess_naver_state = ($this->session->userdata('naver_state'))? $this->session->userdata('naver_state'):$_SESSION['naver_state'];
			$sess_naver_access_token = ($this->session->userdata('naver_access_token'))? $this->session->userdata('naver_access_token'):$_SESSION['naver_access_token'];

			if($this->arrSns['use_n']) {//naver 사용여부

				$mtype = $sess_mtype;
				$naveruserprofile = $this->snssocial->naveraccount( $mtype, 'join');
				if( !$sess_naver_access_token ||  !$sess_naver_state ) {
					$this->session->unset_userdata('user_accesstoken');
					$this->session->unset_userdata('naver_state');
					$this->session->unset_userdata('http_host');
					$this->session->unset_userdata('nvuser');
					$this->session->unset_userdata('mtype');
					$result = false;
					$msg	= "잘못된 접근입니다.";
				}else{

					if( $naveruserprofile['enc_id']) {

						$params['rute']				= 'naver';
						$params['email']			= $naveruserprofile['email'];
						$params['userid']			= $naveruserprofile['email'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'nv_'.$params['userid'];

						$params['sns_n']			= $naveruserprofile['enc_id'];
						$params['password']			= '';
						$params['user_name']		= $naveruserprofile['nickname'];//
						$params['email']			= $naveruserprofile['email'];
						$params['sms']				= 'n';

						if($naveruserprofile['gender'])
							$params['sex']			= ($naveruserprofile['gender']=='F')?'female':'male';//Man

						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']			= ($mtype == 'biz')?true:false;
						$params['regist_date']		= date('Y-m-d H:i:s');

						if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 네이버 통합하기
							$where_arr = array('sns_n'=>$params['sns_n']);
							$mbdata = get_data('fm_member', $where_arr);
							if(!$mbdata){//회원찾기
								$snsintergration = $this->sns_Integration_direct_ok($params);
								if($snsintergration) {
									$this->sns_login_auth('sns_n');	//로그인세션추가
									$result		= true;
									$retururl	= '/mypage/sns';
								}else{//통합실패
									$result = false;
									$msg	= "잘못된 접근입니다.";
								}
							}else{//중복체크
								$this->session->unset_userdata('nvuser');
								$this->session->unset_userdata('naver_access_token');
								$result = false;
								$msg	= "이미 가입된 네이버 계정 입니다.<br />다른 네이버 계정으로 가입해 주세요.";
							}
						}else{
							$where_arr = array('sns_n'=>$params['sns_n']);//
							$data = get_data('fm_member', $where_arr);
							if(!$data){ 
								//정보가 없을 경우 가입후 로그인하기
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									if($this->app_status == "hold"){
										$result = false;
										$msg	= $params['user_name']."님은 아직 가입승인되지 않았습니다.";
									}else{
										$snslogin = $this->sns_login($params,'sns_n');
										if($snslogin) {
											$result = true;
											$retururl = '../';
										}else{//로그인실패시
											$result = false;
											$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
										}
									}
								}else{//가입실패시
									$this->session->unset_userdata('mtype');
									$this->session->unset_userdata('nvuser');
									$this->session->unset_userdata('http_host');
									$this->session->unset_userdata('naver_access_token');
									$result = false;
									$msg	= "이미 가입했거나 탈퇴회원입니다.";
								}
							}else{
								if($data[0]['status'] == 'hold'){ //이미 가입된경우 로그인하기
									$result = false;
									$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
								}else{
									$snslogin = $this->sns_login($params,'sns_n');
									$this->session->unset_userdata('mtype');
									if($snslogin) {
										$result		= true;
										$retururl	= '../';
										//$return = array('result'=>true,'retururl'=>'/mypage/');
									}else{//로그인실패시
										$result = false;
										$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
									}
								}
							}
						}
					}else{
						$this->session->unset_userdata('user_accesstoken');
						$this->session->unset_userdata('naver_state');
						$this->session->unset_userdata('nvuser');
						$this->session->unset_userdata('http_host');
						$this->session->unset_userdata('mtype');
						$result = false;
						$msg	= "잘못된 접근입니다.";
					}
				}
			}else{
				$this->session->unset_userdata('user_accesstoken');
				$this->session->unset_userdata('naver_state');
				$this->session->unset_userdata('nvuser');
				$this->session->unset_userdata('http_host');
				$this->session->unset_userdata('mtype');
				$result = false;
				$msg	= "네이버 로그인 연동 사용 여부를 확인해 주세요.";
			}

			$return = array("result"=>$result,"msg"=>$msg,"retururl"=>$retururl);

			echo json_encode($return);
			exit;

		}

		//naver 쇼핑몰로그인하기 (새창)
		public function naverlogin() {

			$sess_nvuser		= ($this->session->userdata('nvuser'))? $this->session->userdata('nvuser'):$_SESSION['nvuser'];
			$sess_naver_access_token = ($this->session->userdata('naver_access_token'))? $this->session->userdata('naver_access_token'):$_SESSION['naver_access_token'];

			if($this->arrSns['use_n']) {//naver 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$naveruserprofile = $this->snssocial->naveraccount( $mtype, 'login');
				if( !$sess_naver_access_token ||  !$sess_nvuser ) {
					$result = false;
					$msg	= "잘못된 접근입니다.1";
					$this->session->unset_userdata('http_host');
				}else{
					if( $naveruserprofile['enc_id']) {
						$params['rute']				= 'naver';
						$params['email']			= $naveruserprofile['email'];
						$params['userid']			= $naveruserprofile['email'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query		= $this->db->get("fm_member");
						$mem_chk 	= $query->result_array();

						if($mem_chk) $params['userid'] = 'nv_'.$params['userid'];

						$params['sns_n']			= $naveruserprofile['enc_id'];	//사용자확인값
						$params['password']			= '';
						$params['user_name']		= $naveruserprofile['nickname'];//
						$params['email']			= '';
						$params['sms']				= 'n';

						if($naveruserprofile['gender'])
							$params['sex']			= ($naveruserprofile['gender']=='F')?'female':'male';//Man

						$params['birthday']			= date("Y")."-".$naveruserprofile['birthday'];
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']				= ($mtype == 'biz')?true:false;
						$params['regist_date']	= date('Y-m-d H:i:s');
						$where_arr = array('sns_n'=>$params['sns_n']);//
						$data = get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$result = false;
							$msg	= "일치하는 회원정보가 없습니다.<br />회원가입 후 이용해 주세요";
						}else{//가입된경우 로그인하기

							if($data[0]['status'] == 'hold'){
								$result = false;
								$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
							}else{
								$snslogin = $this->sns_login($params,'sns_n');
								if($snslogin) {
									$result		= true;
									$msg		= "";
									$retururl	= '../';
								}else{//로그인실패시
									$result = false;
									$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다.3 ".implode("<br>->",$naveruserprofile);
					}
				}
			}else{
				$result = false;
				$msg	= "잘못된 접근입니다.4";
			}
			$return = array('result'=>$result,'msg'=>$msg);

			echo json_encode($return);
			exit;
		}
	/**
	@ naver api end
	------------------------------------------------------------
	**/

	/**
	@ kakao login api start
	------------------------------------------------------------
	**/

		// kakao 사용확인 및 설정 key 값 불러오기
		public function kakaokeys(){
			$this->arrSns = ($this->arrSns)?$this->arrSns:config_load('snssocial');
			if($this->arrSns['use_k']) $return = array('result'=>true,'keys'=>$this->arrSns['key_k']);
				else $return = array('result'=>false,'keys'=>"");
			echo json_encode($return);
		}

		//kakao 아이디로 회원가입
		public function kakaojoin() {

			if($this->arrSns['use_k']) {//kakao 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$this->snssocial->setkakaouser($_POST);
				$kakaouserprofile = $this->snssocial->kakaoaccount($mtype,'join');
				if( !$_POST['access_token'] || !$_POST['refresh_token']) {
					$result = false;
					$msg	= "잘못된 접근입니다. 1";
				}else{

					if( $_POST['id']) {
						$params['rute']				= 'kakao';
						$params['userid']			= "kko".$_POST['id']; //사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'kko_'.$params['userid'];

						$params['sns_k']			= $_POST['id'];
						$params['password']			= '';
						$params['user_name']		= ($kakaouserprofile['nickname'])? $kakaouserprofile['nickname']:'';
						$params['nickname']			= ($kakaouserprofile['nickname'])? $kakaouserprofile['nickname']:'';
						$params['sms']				= 'n';

						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['sex']				= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']			= ($mtype == 'biz')?true:false;
						$params['regist_date']		= date('Y-m-d H:i:s');

						if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 카카오 통합하기
							$where_arr	= array('sns_k'=>$params['sns_k']);
							$mbdata		= get_data('fm_member', $where_arr);
							if(!$mbdata){//회원찾기
								$snsintergration = $this->sns_Integration_direct_ok($params);
								if($snsintergration) {
									$this->sns_login_auth('sns_k');	//로그인세션추가
									$result		= true;
									$msg		= "";
									$retururl	= '/mypage/myinfo';
								}else{//통합실패
									$result = false;
									$msg	= "잘못된 접근입니다2.";
								}
							}else{//중복체크
								if($mbdata[0]['status']=='hold'){
									$result = false;
									$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
								}else{
									$result = false;
									$msg	= "이미 가입된 카카오 계정 입니다.<br />다른 카카오 계정으로 가입해 주세요.";
								}
							}
						}else{
							
							$where_arr = array('sns_k'=>$params['sns_k']);//
							$data = get_data('fm_member', $where_arr);
							if(!$data) {//정보가 없을 경우 가입후 로그인하기
								$snsregister = $this->sns_register_ok($params);
								if($snsregister) {
									if($this->app_status == "hold"){
										$result = false;
										$msg	= $params['user_name']."님은 아직 가입승인되지 않았습니다.";
									}else{
										$snslogin = $this->sns_login($params,'sns_k');
										if($snslogin) {
											$result		= true;
											$retururl	= '../';
										}else{//로그인실패시
											$result = false;
											$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
										}
									}
								}else{//가입실패시
									$result = false;
									$msg	= "이미 가입했거나 탈퇴회원입니다.";
								}
							}else{//이미가입된경우 로그인하기
									
								if($data[0]['status'] == 'hold'){
									$result = false;
									$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
								}else{
									$snslogin = $this->sns_login($params,'sns_k');
									$this->session->unset_userdata('mtype');
									if($snslogin) {
										$result		= true;
										$retururl	= '../';
									}else{//로그인실패시
										$result = false;
										$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
									}
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다22.";
					}
				}
			}else{
				$result = false;
				$msg	= "잘못된 접근입니다1.";
			}
			$return = array('result'=>$result,'msg'=>$msg,'retururl'=>$retururl);
			echo json_encode($return);
			exit;
		}

		//kakao 아이디로 로그인하기
		public function kakaologin() {

			if($this->arrSns['use_k']) {//kakao 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$this->snssocial->setkakaouser($_POST);
				$kakaouserprofile = $this->snssocial->kakaoaccount($mtype,'join');
				if( !$_POST['access_token'] || !$_POST['refresh_token']) {
					$result = false;
					$msg	= "잘못된 접근입니다1.";
				}else{

					if( $_POST['id']) {
						$params['rute']				= 'kakao';
						$params['userid']			= "kko".$_POST['id']; //사용자아이디

						$this->db->where('userid', $params['userid']);
						$query = $this->db->get("fm_member");
						$mem_chk = $query->result_array();
						if($mem_chk) $params['userid'] = 'kko_'.$params['userid'];

						$params['sns_k']			= $_POST['id'];	//사용자확인값
						$params['password']			= '';
						$params['user_name']		= $kakaouserprofile['nickname'];//
						$params['email']			= '';
						$params['sms']				= 'n';

						$params['birthday']			= '';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']				= ($mtype == 'biz')?true:false;
						$params['regist_date']	= date('Y-m-d H:i:s');
						$where_arr = array('sns_k'=>$params['sns_k']);//
						$data = get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$result = false;
							$msg	= "일치하는 회원정보가 없습니다.<br />회원가입 후 이용해 주세요";
						}else{//가입된경우 로그인하기

							if($data[0]['status'] == 'hold'){
								$result = false;
								$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
							}else{
								$snslogin = $this->sns_login($params,'sns_k');
								if($snslogin) {
									$result		= true;
									$retururl	= '../';
								}else{//로그인실패시
									$result = false;
									$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다.3 ".implode("<br>->",$kakaouserprofile);
					}
				}
			}else{
				$result = false;
				$msg	= "잘못된 접근입니다4.";
			}

			$return = array('result'=>$result,'msg'=>$msg,'retururl'=>$retururl);
			echo json_encode($return);
			exit;
		}

	/**
	@ kakao login api end
	------------------------------------------------------------
	**/

	/**
	@ daum api start (OAuth 2.0)
	------------------------------------------------------------
	** 순서
	** join_gate > agreement > daumloginck(sns_process) > daumuserck(sns_process:access_token넘어옴) > 
	**/
		//daum 로그인체크 (본래창) //create login url
		public function daumloginck() {
			if($this->arrSns['use_d']) {//daum 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];//member, business
				$mform = ($_POST['mform'])?$_POST['mform']:$_GET['mform'];//join, login
				$loginurl = $this->snssocial->daumloginurl($mtype,$mform);

				if($loginurl) {
					if($_POST['m'] == "myinfo") $loginurl = urlencode($loginurl);
					$return = array('result'=>true, 'loginurl'=>$loginurl);
					echo json_encode($return);
					exit;
				}else{
					$return = array('result'=>false, 'msg'=>'다음(Daum)에서 회원정보를 가져오지 못하였습니다.<br/>관리자에게 문의해 주세요.');
					echo json_encode($return);
					exit;
				}
			}else{
				$return = array('result'=>false, 'msg'=>'잘못된 접근입니다.');
				echo json_encode($return);
				exit;
			}
		}

		## callback url
		public function daumuserck() {

			$sess_http_host	= ($this->session->userdata('http_host'))? $this->session->userdata('http_host'):$_SESSION['http_host'];

			# accesstoken
			if($_SERVER['HTTP_HOST'] != $sess_http_host && $sess_http_host){
				## 모바일의 경우 : 모바일 전용 url 로   리다이렉트
				echo js("document.location.href=\"http://{$sess_http_host}/sns_process/daumuserck?\"+window.location.hash+\"\"");
			}else{
				echo js("self.close(); opener.daumaccess(window.location.hash); ");
			}
			exit;

		}

		public function daumuserinfo(){

			$sess_mtype		= ($this->session->userdata('mtype'))? $this->session->userdata('mtype'):$_SESSION['mtype'];
			$sess_dmuser	= ($this->session->userdata('dmuser'))? $this->session->userdata('dmuser'):$_SESSION['dmuser'];

			if($this->arrSns['use_d']) {//daum 사용여부
				//$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$mtype = $sess_mtype;
				$daumuserprofile = $this->snssocial->daumuserprofile($mtype, 'join');
				if( !$sess_dmuser ) {
					$return = array('result'=>true, 'message'=>"회원 정보를 불러올 수 없습니다");
					echo json_encode($return);
					exit;
				}else{
					if($daumuserprofile) {
						$return = array('result'=>true, 'message'=>"성공");
						echo json_encode($return);
						exit;
						//echo js(" self.close(); opener.daumjoinlogin(); ");
						//exit;
					}else{
						$return = array('result'=>false, 'message'=>"잘못된 접근입니다.");
						echo json_encode($return);
						exit;
					}
				}
			}else{
				$return = array('result'=>false, 'message'=>"잘못된 접근입니다1.");
				echo json_encode($return);
				exit;
			}
		}


		//daum 쇼핑몰회원가입 (새창)
		public function daumjoin() {

			$sess_mtype		= ($this->session->userdata('mtype'))? $this->session->userdata('mtype'):$_SESSION['mtype'];

			if($this->arrSns['use_d']) {//daum 사용여부
				$mtype = $sess_mtype;
				$daumuserprofile = $this->snssocial->daumaccount( $mtype, 'join');

				if( $daumuserprofile['id']) {
					$params['rute']				= 'daum';
					$params['email']			= '';
					$params['userid']			= 'dm'.$daumuserprofile['id'];//사용자아이디

					$this->db->where('userid', $params['userid']);
					$query		= $this->db->get("fm_member");
					$mem_chk	= $query->result_array();
					if($mem_chk) $params['userid'] = 'dm_'.$params['userid'];

					$params['sns_d']			= $daumuserprofile['id'];
					$params['password']			= '';
					$params['user_name']		= $daumuserprofile['nickname'];//
					$params['nickname']			= $daumuserprofile['nickname'];//
					$params['email']			= '';
					$params['sms']				= 'n';
					$params['sex']				= 'none';
					$params['birth_type']		= 'none';
					$params['status']			= $this->app_status;
					$params['emoney']			= 0;
					$params['login_cnt']		= 0;
					$params['order_cn']	 		= 0;
					$params['order_sum']		= 0;
					$params['mtype']			= ($mtype == 'biz')?true:false;
					$params['regist_date']		= date('Y-m-d H:i:s');

					if($_POST['facebooktype'] == 'mbconnect_direct') {//로그인된 상태에서 다음 통합하기
						$where_arr = array('sns_d'=>$params['sns_d'], 'status'=>'done');
						$mbdata = get_data('fm_member', $where_arr);
						if(!$mbdata){//회원찾기
							$snsintergration = $this->sns_Integration_direct_ok($params);
							if($snsintergration) {
								$this->sns_login_auth('sns_d');	//로그인세션추가
								$result		= true;
								$retururl	= "/mypage/myinfo";
							}else{//통합실패
								$result		= false;
								$msg		= "잘못된 접근입니다2.";
							}
						}else{//중복체크
							$this->session->unset_userdata('dmuser');
							$result		= false;
							$msg		= "이미 가입된 다음(Daum) 계정 입니다.<br />다른 다음(Daum) 계정으로 가입해 주세요.";
						}
					}else{
						
						$where_arr = array('sns_d'=>$params['sns_d']);
						$data = get_data('fm_member', $where_arr);

						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$snsregister = $this->sns_register_ok($params);
							if($snsregister) {
								if($this->app_status == "hold"){
									$result = false;
									$msg	= $params['user_name']."님은 아직 가입승인되지 않았습니다.";
								}else{
									$snslogin = $this->sns_login($params,'sns_d');
									if($snslogin) {
										$result		= true;
										$retururl	= '../';
									}else{//로그인실패시
										$result = false;
										$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
									}
								}
							}else{//가입실패시
								$this->session->unset_userdata('mtype');
								$this->session->unset_userdata('dmuser');
								$result = false;
								$msg	= "이미 가입했거나 탈퇴회원입니다.";
							}
						}else{//이미가입된경우 로그인하기
							if($data[0]['status'] == 'hold'){
								$result = false;
								$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
							}else{
								$snslogin = $this->sns_login($params,'sns_d');
								$this->session->unset_userdata('mtype');
								if($snslogin) {
									$result		= true;
									$retururl	= '../';
								}else{//로그인실패시
									$result = false;
									$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
								}
							}
						}
					}
				}else{
					$this->session->unset_userdata('dmuser');
					$this->session->unset_userdata('mtype');
					$result = false;
					$msg	= "잘못된 접근입니다.";
				}
			
			}else{
				$this->session->unset_userdata('dmuser');
				$this->session->unset_userdata('mtype');
				$result = false;
				$msg	= "다음 로그인 연동 사용 여부를 확인해 주세요.";
			}

			## 웹url과 모바일url의 씽크를 위해 생성했던 세션은 삭제
			$this->session->unset_userdata('http_host');

			$return = array("result"=>$result,"msg"=>$msg,"retururl"=>$retururl);

			echo json_encode($return);
			exit;

		}

		//daum 쇼핑몰로그인하기 (새창)
		public function daumlogin() {

			$sess_dmuser	= ($this->session->userdata('dmuser'))? $this->session->userdata('dmuser'):$_SESSION['dmuser'];
			$sess_daum_access_token = ($this->session->userdata('daum_access_token'))? $this->session->userdata('daum_access_token'):$_SESSION['daum_access_token'];


			if($this->arrSns['use_d']) {//daum 사용여부
				$mtype = ($_POST['mtype'])?$_POST['mtype']:$_GET['mtype'];
				$daumuserprofile = $this->snssocial->daumaccount( $mtype, 'login');
				if( !$sess_daum_access_token ||  !$sess_dmuser ) {
					$result = false;
					$msg	= "잘못된 접근입니다.1";
				}else{
					if( $daumuserprofile['id']) {
						$params['rute']				= 'daum';
						$params['email']			= '';
						$params['userid']			= 'dm'.$daumuserprofile['id'];//사용자아이디

						$this->db->where('userid', $params['userid']);
						$query		= $this->db->get("fm_member");
						$mem_chk	= $query->result_array();
						if($mem_chk) $params['userid'] = 'dm_'.$params['userid'];

						$params['sns_d']			= $daumuserprofile['id'];	//사용자확인값
						$params['password']			= '';
						$params['user_name']		= $daumuserprofile['nickname'];//
						$params['email']			= '';
						$params['sms']				= 'n';
						$params['sex']				= 'none';
						$params['birth_type']		= 'none';
						$params['status']			= $this->app_status;
						$params['emoney']			= 0;
						$params['login_cnt']		= 0;
						$params['order_cn']	 		= 0;
						$params['order_sum']		= 0;
						$params['mtype']			= ($mtype == 'biz')?true:false;
						$params['regist_date']		= date('Y-m-d H:i:s');
						$where_arr					= array('sns_d'=>$params['sns_d']);//
						$data						= get_data('fm_member', $where_arr);
						if(!$data) {//정보가 없을 경우 가입후 로그인하기
							$result = false;
							$msg	= "일치하는 회원정보가 없습니다.<br />회원가입 후 이용해 주세요";
						}else{//가입된경우 로그인하기
							if($data[0]['status'] == 'hold'){
								$result = false;
								$msg	= $data[0]['user_name']."님은 아직 가입승인되지 않았습니다.";
							}else{
								$snslogin = $this->sns_login($params,'sns_d');
								if($snslogin) {
									$result		= true;
									$msg		= "";
									$retururl	= '../';
								}else{//로그인실패시
									$result = false;
									$msg	= "탈퇴회원입니다.<br />관리자에게 문의해 주세요.";
								}
							}
						}
					}else{
						$result = false;
						$msg	= "잘못된 접근입니다.3 ".implode("<br>->",$cyworlduserprofile);
					}
				}
			}else{
				$result = false;
				$msg	= "잘못된 접근입니다.4";
			}
			$return = array('result'=>$result,'msg'=>$msg,'retururl'=>$retururl);
			echo json_encode($return);
			exit;
		}
	/**
	@ daum api end
	------------------------------------------------------------
	**/

	/**
	@ google+ api start
	------------------------------------------------------------
	**/
		//google+ loginck(본래창)
		public function googleloginck(){
		}

		//google+ loginck callbackurl(새창)
		public function googleuserck(){
		}

		//google+ join (본래창)
		public function googlejoin() {
		}

		//google+ login (본래창)
		public function googlelogin(){
		}
	/**
	@ google+ api end
	------------------------------------------------------------
	**/


	/**
	@ mypeople api start
	------------------------------------------------------------
	**/
		//mypeople loginck(본래창)
		public function mypeopleloginck(){
		}

		//mypeople loginck callbackurl(새창)
		public function mypeopleuserck(){
		}

		//mypeople join (본래창)
		public function mypeoplejoin() {
		}

		//mypeople login (새창)
		public function mypeoplelogin(){
		}
	/**
	@ mypeople api end
	------------------------------------------------------------
	**/

	/**
	* @sns 으로 로그인시
	* @
	*/
	function sns_login($params,$snstype){
		$where_arr	= array($snstype=>$params[$snstype], 'status'=>'done');
		$data		= get_data('fm_member', $where_arr);
		$mbparams	= $data[0];
		if($mbparams) {
			if($params['email'] != $params['email']) $emailup = " email = '".$params['email']."', ";
			$qry = "update fm_member set {$emailup} login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$mbparams['member_seq']}' ";
			$result = $this->db->query($qry);

			if($params['email'] != $params['email']) {
				### Private Encrypt
				$email = get_encrypt_qry('email');
				$cellphone = get_encrypt_qry('cellphone');
				$sql = "update fm_member set {$email},{$cellphone} where member_seq = '{$mbparams['member_seq']}' ";
				$this->db->query($sql);
			}

			## sns 로그인/연동 계정 세션 저장
			$this->sns_login_auth($snstype);

			$this->Snsmemberck($params, 'sns_'.substr($params['rute'],0,1), $mbparams);//sns회원가입추가

			### SESSION
			$this->create_member_session($mbparams);

			### 장바구니 MERGE
			$this->load->model('cartmodel');
			$this->cartmodel->merge_for_member($mbparams['member_seq']);

			//fblike 할인 MERGE
			$this->db->where('session_id',$this->session->userdata('session_id'));
			$this->db->update('fm_goods_fblike', array('member_seq' => $mbparams['member_seq']));

			### 로그인 이벤트
			$this->load->model('joincheckmodel');
			$jcresult = $this->joincheckmodel->login_joincheck($mbparams['member_seq']);

			/* 고객리마인드서비스 : 상세유입로그 */
			$this->load->helper('reservation');
			$curation = array("action_kind"=>"login_sns");
			$res = curation_log($curation);


		}else{
		}
		return $mbparams;
	}

	## 로그인/가입연동한 sns계정 세션 저장
	function sns_login_auth($snstype){

		$this->load->model('membermodel');
		$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
		if($this->mdata['rute'] != 'none' && !$this->session->userdata("snslogn")){
			switch($snstype){
				case "sns_f": $snstype = "facebook"; break;
				case "sns_t": $snstype = "twitter"; break;
				case "sns_c": $snstype = "cyworld"; break;
				case "sns_n": $snstype = "naver"; break;
				case "sns_m": $snstype = "me2day"; break;
				case "sns_k": $snstype = "kakao"; break;
				case "sns_d": $snstype = "daum"; break;
			}
			$this->session->set_userdata("snslogn",$snstype);
		}
	}

	/**
	* SNS 회원통합하기
	* @
	*/
	function sns_Integration_ok($params){
		$this->load->model('ssl');
		$this->ssl->decode();

		### Validation
		$this->validation->set_rules('userid', '아이디','trim|required|max_length[20]|xss_clean');
		$this->validation->set_rules('password', '비밀번호','trim|required|max_length[32]|xss_clean');
		if($this->validation->exec()===false){
			$err = $this->validation->error_array;
			$return = array('result'=>false, 'msg'=>$err['value']);
			echo json_encode($return);
			exit;
		}

		### QUERY
		$where_arr = array('userid'=>$_POST['userid'], 'password'=>md5($_POST['password']));
		$data = get_data('fm_member', $where_arr);
		if(!$data){
			$return = array('result'=>false, 'msg'=>"일치하는 회원정보가 없습니다.");
			echo json_encode($return);
			exit;
		}

		### LOG
		$mbparams = $data[0];
		$snstype = " sns_".substr($params['rute'],0,1)."='".$params['sns_'.substr($params['rute'],0,1)]."', ";

		$qry = "update fm_member set ".$snstype." login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$mbparams['member_seq']}'";
		$result = $this->db->query($qry);

		$this->Snsmemberck($params, 'sns_'.substr($params['rute'],0,1), $mbparams);//sns회원가입추가

		### SESSION
		$this->create_member_session($mbparams);

		### 장바구니 MERGE
		$this->load->model('cartmodel');
		$this->cartmodel->merge_for_member($mbparams['member_seq']);

		//fblike 할인 MERGE
		$this->db->where('session_id',$this->session->userdata('session_id'));
		$this->db->update('fm_goods_fblike', array('member_seq' => $mbparams['member_seq']));


		### 로그인 이벤트
		$this->load->model('joincheckmodel');
		$jcresult = $this->joincheckmodel->login_joincheck($mbparams['member_seq']);

		return $mbparams;
	}


	/**
	* SNS 회원통합하기
	* @
	*/
	function sns_Integration_direct_ok($params){
		$this->load->model('ssl');
		if($this->userInfo['member_seq']){//로그인된경우에만 체크
			$where_arr = array('member_seq'=>$this->userInfo['member_seq']);
			$mbdata = get_data('fm_member', $where_arr);
			$mbparams = $mbdata[0];

			$snstype = " sns_".substr($params['rute'],0,1)."='".$params['sns_'.substr($params['rute'],0,1)]."' ";
			$qry = "update fm_member set ".$snstype." where member_seq = '{$mbparams['member_seq']}'";
			$result = $this->db->query($qry);

			$this->Snsmemberck($params, 'sns_'.substr($params['rute'],0,1), $mbparams );//sns회원가입추가
		}else{
			$snswhere_arr = array('session_id'=>$this->session->userdata('session_id'));
			$snsjoindataar = get_data('fm_membersns_join', $snswhere_arr);
			$snsjoindata = $snsjoindataar[0];
			if($snsjoindata['member_seq']){
				$where_arr = array('member_seq'=>$snsjoindata['member_seq']);
				$mbdata = get_data('fm_member', $where_arr);
				$mbparams = $mbdata[0];

				$snstype = " sns_".substr($params['rute'],0,1)."='".$params['sns_'.substr($params['rute'],0,1)]."' ";
				$qry = "update fm_member set ".$snstype." where member_seq = '{$mbparams['member_seq']}'";
				$result = $this->db->query($qry);

				$this->Snsmemberck($params, 'sns_'.substr($params['rute'],0,1), $mbparams );//sns회원가입추가
			}
		}
		return $mbdata;
	}

	/**
	* @ sns 회원통합 > 해제하기
	* @
	**/
	function facebookdisconnect() {
		$where_arr = array('member_seq'=>$this->userInfo['member_seq']);
		$mbdata = get_data('fm_member', $where_arr);
		$mbparams = $mbdata[0];
		if($mbparams['sns_f']){
			$qry = "update fm_member set sns_f='' where member_seq = '{$this->userInfo['member_seq']}'";
			$result = $this->db->query($qry);
			$this->load->model('snsmember');
			$this->snsmember->snsmb_delete($mbparams['sns_f'], 'facebook');//sns회원삭제
			$this->session->unset_userdata('fbuser');
			$this->session->unset_userdata('accesstoken');
			$this->session->unset_userdata('signedrequest');

			## 로그인/연동sns계정 세션 해제 시작
			$this->snsdisconnect_auth("sns_f");
			## 로그인/연동 sns계쩡 세션 해제 끝

			$return = array('result'=>$result, 'msg'=>"Facebook계정의 연결이 해제되었습니다.");
			echo json_encode($return);
		}else{
			$return = array('result'=>false, 'msg'=>"이미 해제된 상태입니다..");
			echo json_encode($return);
		}
		exit;
	}

	/**
	* @ sns 회원통합 > 해제하기
	* @
	**/
	function snsdisconnect() {//회원통합 기본
		$snstype = ($_POST['snstype'])?$_POST['snstype']:'sns_f';
		$snsrute = ($_POST['snsrute'])?$_POST['snsrute']:'facebook';

		$where_arr = array('member_seq'=>$this->userInfo['member_seq']);
		$mbdata = get_data('fm_member', $where_arr);
		$mbparams = $mbdata[0];
		if($mbparams[$snstype]){
			$qry = "update fm_member set ".$snstype."='' where member_seq = '{$this->userInfo['member_seq']}'";
			$result = $this->db->query($qry);
			$this->load->model('snsmember');
			$this->snsmember->snsmb_delete($mbparams[$snstype], $snsrute);//sns회원삭제

			## 로그인/연동sns계정 세션 해제 시작
			$this->snsdisconnect_auth($snstype);
			## 로그인/연동 sns계쩡 세션 해제 끝

			$return = array('result'=>$result, 'msg'=>"정상적으로 해제되었습니다.");
			echo json_encode($return);
		}else{
			$return = array('result'=>false, 'msg'=>"이미 해제된 상태입니다..");
			echo json_encode($return);
		}
		exit;
	}

	## sns 연동해제에 따른 sns로그인 계정 교체
	function snsdisconnect_auth($snsrute){

		switch($snsrute){
			case "sns_f": 
				$snstype = "facebook";
			break;
			case "sns_t": 
				$snstype = "twitter";
				$this->session->unset_userdata('twuser');
				$this->session->unset_userdata('oauth_token');
				$this->session->unset_userdata('oauth_token_secret');
			break;
			case "sns_c":
				$this->session->unset_userdata('cyuser');
				$this->session->unset_userdata('cyworld_request_token_secret');
				$snstype = "cyworld";
			break;
			case "sns_m":
				$snstype = "me2day";
			break;
			case "sns_n":
				$snstype = "naver";
				$this->session->unset_userdata('naver_access_token');
				$this->session->unset_userdata('naver_state');
				$this->session->unset_userdata('nvuser');
			break;
			case "sns_k":
				$snstype = "kakao";
				$this->session->unset_userdata('kkouser');
			break;
			case "sns_d":
				$snstype = "daum";
				$this->session->unset_userdata('dmuser');
				$this->session->unset_userdata('daum_access_token');
				$this->session->unset_userdata('http_host');
			break;
		}

		## 연결해제할 sns계정과 현재 로그인한 계정이 같을 시 남아 있는 sns 계정으로 교체
		if($this->session->userdata("snslogn") == $snstype){
			## 남아 있는 sns 계정 정보를 불러온다
			$sql	= "select rute,sns_f,email from fm_membersns where member_seq='".$this->userInfo['member_seq']."' and rute!='".$snsrute."' and rute!='' order by seq asc limit 1";
			$query		= $this->db->query($sql);
			$result		= $query->result_array();
			$next_sns	= $result[0]['rute'];
			$next_sns_f = ($result[0]['email'])? "_".$result[0]['email']:$result[0]['sns_f'];

			switch($next_sns){
				case "facebook": $next_rute = "fb"; break;
				case "twitter": $next_rute = "tw"; break;
				case "cyworld": $next_rute = "cy"; break;
				case "me2day":	$next_rute = "m2"; break;
				case "naver":	$next_rute = "nv"; break;
				case "kakao":	$next_rute = "kk"; break;
				case "daum":	$next_rute = "dm"; break;
			}

			
			$this->load->model('membermodel');
			$this->mdata = $this->membermodel->get_member_data($this->userInfo['member_seq']);//회원정보
			if($this->mdata['rute'] != 'none'){
				$userid = $next_rute."_".$next_sns_f."_".mktime();
				$rute	= $next_sns;

				## 남아있는 sns 계정으로 id 교체
				$this->db->query('update fm_member set userid=?,rute=? where member_seq=? ',array($userid,$rute,$this->userInfo['member_seq']));
				$sess_user = ( $this->session->userdata('user') )?$this->session->userdata('user'):$_SESSION['user'];

				if($snstype == "naver") {
					$sess_user['rute'] = "a";
				}else{
					$sess_user['rute'] = substr($snstype,0,1);
				}
				$sess_user['userid'] = $userid;
				$this->session->set_userdata('user',$sess_user);
			}


			$this->session->set_userdata('snslogn',$next_sns);
		}

	}


	/**
	* SNS 신규 회원가입
	* @ $params userid : sns id
	* @ $params password
	* @ $params user_name
	* @ $params rute enum('facebook', 'twiter', 'none')
	* @ $params sms enum('y', 'n')
	* @ $params sex enum('male', 'female', 'none')
	* @ $params birth_type enum('sola', 'luna', 'none')
	* @ $params status enum('done', 'hold', 'withdrawal')
	* @ $params emoney, login_cnt, order_cnt , order_sum,
	* @ $params lastlogin_date, regist_date, update_date, grade_update_date
	* @ $params auth_type enum('none', 'auth', 'ipin')
	* @
	*/
	function sns_register_ok($params){
		###
		$params['group_seq']	= '1';
		$params['password']	= md5($params['password']);
		$params['marketplace'] = !empty($_COOKIE['marketplace']) ? $_COOKIE['marketplace'] : '';//유입매체

		###
		$auth = $this->session->userdata('auth');
		if(isset($auth) && $auth['auth_yn']){
			$params['auth_type']	= $auth['namecheck_type'];
			$params['auth_code']	= $auth['namecheck_check'];
			if($params['auth_type'] != "safe"){//"ipin", "phone"
				$params['auth_vno']		= $auth['namecheck_vno'];
			}else{
				$params['auth_vno']		= $auth['namecheck_key'];
			}
		}

		//초대
		$params['fb_invite']	= $this->session->userdata('fb_invite');

		$data = filter_keys($params, $this->db->list_fields('fm_member'));
		$result = $this->db->insert('fm_member', $data);
		$memberseq = $this->db->insert_id();
		$params['member_seq'] = $memberseq;
		$this->Snsmemberck($params, 'sns_'.substr($params['rute'],0,1));//sns회원가입추가

		// 회원 가입 통계 저장
		$this->load->model('statsmodel');
		$this->statsmodel->insert_member_stats($memberseq,$params['birthday'],$params['address'],$params['sex']);

		if($params['mtype']){//기업회원인경우
			$bdata = filter_keys($params, $this->db->list_fields('fm_member_business'));
			$this->db->insert('fm_member_business', $bdata);
		}

		### Private Encrypt
		$email = get_encrypt_qry('email');
		$cellphone = get_encrypt_qry('cellphone');
		$sql = "update fm_member set {$email}, {$cellphone},  update_date = now() where member_seq = {$memberseq}";//, {$cellphone}, {$phone}
		$this->db->query($sql);

		###
		if($result){//join success
			###
			$app = config_load('member');
			if($app['autoApproval']=='Y'){//자동승인

				$this->load->model('emoneymodel');
				$this->load->model('pointmodel');

				### 특정기간
				if($app['start_date'] && $app['end_date']){
					$today = date("Y-m-d");
					if($today>=$app['start_date'] && $today<=$app['end_date']){
						$app['emoneyJoin']	= $app['emoneyJoin_limit'];
						$app['pointJoin']	= $app['pointJoin_limit'];
					}
				}

				if($app['emoneyJoin']){
					$emoney['type'] 	= 'join';
					$emoney['emoney'] 	= $app['emoneyJoin'];
					$emoney['gb']		= 'plus';
					$emoney['memo']		= '회원 가입 적립금';
					$emoney['limit_date'] = get_emoney_limitdate('join');
					$this->membermodel->emoney_insert($emoney, $memberseq);
				}

				if($app['pointJoin']){
					### POINT
					$iparam['gb']			= "plus";
					$iparam['type']			= 'join';
					$iparam['point']		= $app['pointJoin'];
					$iparam['memo']			= '회원 가입 포인트';
					$iparam['limit_date']	= get_point_limitdate('join');
					$this->membermodel->point_insert($iparam, $memberseq);
				}

				//추천시
				if($params['recommend']){
					$chk = get_data("fm_member",array("userid"=>$params['recommend'],"status"=>"done"));
					if(is_array($chk) && $chk[0]['member_seq']) {

						//추천받은자의 추천받은건수 증가 @2013-06-19
						$this->membermodel->member_recommend_cnt($chk[0]['member_seq']);

						//추천 받은 자 -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$emrecommendtock = $this->emoneymodel->get_data($recommendtosc);//추천한 회원 적립금 지급여부
							$maxrecommend = ($app['emoneyLimit']*$app['emoneyRecommend']);

							if( $emrecommendtock['totalcnt'] < $app['emoneyLimit'] && $emrecommendtock['totalemoney'] <= $maxrecommend ) {
								$emoney['type']						= 'recommend_to';
								$emoney['emoney']				= $app['emoneyRecommend'];
								$emoney['gb']						= 'plus';
								$emoney['memo']					= '('.$params['userid'].') 추천 회원 적립금';
								$emoney['limit_date']				= get_emoney_limitdate('recomm');
								$emoney['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}

						if($app['pointRecommend']>0) {
							$recommendtosc['whereis'] = ' and type = \'recommend_to\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$recommendtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalepoint ';
							$pmrecommendtock = $this->pointmodel->get_data($recommendtosc);//추천한 회원 적립금 지급여부
							$maxrecommend = ($app['pointLimit']*$app['pointRecommend']);

							if( $pmrecommendtock['totalcnt'] < $app['pointLimit'] && $pmrecommendtock['totalepoint'] <= $maxrecommend ) {
								$point['type']						= 'recommend_to';
								$point['point']						= $app['pointRecommend'];
								$point['gb']						= 'plus';
								$point['memo']					= '('.$params['userid'].') 추천 회원 포인트';
								$point['limit_date']				= get_point_limitdate('recomm');
								$point['member_seq_to']	= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//추천한자(가입자)
						if($app['emoneyJoiner']>0) {
							unset($emoney);
							$emoney['type']						= 'recommend_from';
							$emoney['emoney']				= $app['emoneyJoiner'];
							$emoney['gb']						= 'plus';
							$emoney['memo']					= '['.$params['recommend'].'] 추천 적립금';
							$emoney['limit_date']				= get_emoney_limitdate('joiner');
							$emoney['member_seq_to']	= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);
						}
						if($app['pointJoiner']>0) {
							unset($point);
							$point['type']							= 'recommend_from';
							$point['point']							= $app['pointJoiner'];
							$point['gb']							= 'plus';
							$point['memo']						= '['.$params['recommend'].'] 추천 포인트';
							$point['limit_date']					= get_point_limitdate('joiner');
							$point['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);
						}
					}
				}

				//초대시
				if($params['fb_invite']) {
					$chk = get_data("fm_member",array("member_seq"=>$params['fb_invite']));
					if($chk[0]['member_seq']) {

						//$fbuserprofile = $this->snssocial->facebooklogin();
						$fbuserprofile = $this->snssocial->facebookuserid();
						if ( !$fbuserprofile ) {
							$this->facebook = new Facebook(array(
							  'appId'  => $this->__APP_ID__,
							  'secret' => $this->__APP_SECRET__,
							  "cookie" => true
							));
							// Get User ID
							$fbuserprofile = $this->facebook->getUser();
							if($fbuserprofile && !$this->session->userdata('fbuser')){
								$this->session->set_userdata('fbuser', $fbuserprofile);
							}else{
								$fbuserprofile = $this->snssocial->facebooklogin();
								if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
									$this->session->set_userdata('fbuser', $fbuserprofile);
								}
							}
						}else{
							if( !$this->session->userdata('fbuser') ) {
								$this->session->set_userdata('fbuser', $fbuserprofile);
							}
						}

						if($fbuserprofile['id']){
							$this->db->where('sns_f', $fbuserprofile['id']);
							$result = $this->db->update('fm_memberinvite', array("joinck"=>'1'));//가입여부 업데이트
						}

						//초대 한 자  -> 제한함
						$todaymonth = date("Y-m");
						if($app['emoneyInvited']>0) {
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(emoney) as totalemoney ';
							$eminvitedtock = $this->emoneymodel->get_data($invitedtosc);//추천한 회원 적립금 지급여부
							$maxinvited = ($app['emoneyLimit_invited']*$app['emoneyInvited']);

							if( $eminvitedtock['totalcnt'] <= $app['emoneyLimit_invited'] && $eminvitedtock['totalemoney'] <= $maxinvited ) {
								unset($emoney);
								$emoney['type']							= 'invite_from';
								$emoney['emoney']					= $app['emoneyInvited'];
								$emoney['gb']							= 'plus';
								$emoney['memo']						= '초대 회원  적립금';
								$emoney['limit_date']					= get_emoney_limitdate('invite_from');
								$emoney['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->emoney_insert($emoney, $chk[0]['member_seq']);
							}
						}
						if($app['pointInvited']>0){
							$invitedtosc['whereis'] = ' and type = \'invite_from\' and gb = \'plus\' and member_seq = \''.$chk[0]['member_seq'].'\'  and regist_date between \''.$todaymonth.'-01 00:00:00\' and \''.$todaymonth.'-31 23:59:59\' ';//
							$invitedtosc['select']	 = ' count(*) as totalcnt, sum(point) as totalpoint ';
							$pminvitedtock = $this->pointmodel->get_data($invitedtosc);//추천한 회원 적립금 지급여부
							$maxinvited = ($app['pointLimit_invited']*$app['pointInvited']);

							if( $pminvitedtock['totalcnt'] <= $app['pointLimit_invited'] && $pminvitedtock['totalpoint'] <= $maxinvited ) {
								unset($point);
								$point['type']							= 'invite_from';
								$point['point']							= $app['pointInvited'];
								$point['gb']							= 'plus';
								$point['memo']						= '초대 회원 포인트';
								$point['limit_date']					= get_point_limitdate('invite_from');
								$point['member_seq_to']		= $memberseq;//2015-02-16
								$this->membermodel->point_insert($point, $chk[0]['member_seq']);
							}
						}

						//초대 받은 자(가입자)
						if($app['emoneyInvitees']>0){
							$emoney['type']							= 'invite_to';
							$emoney['emoney']					= $app['emoneyInvitees'];
							$emoney['gb']							= 'plus';
							$emoney['memo']						= '초대 적립금';
							$emoney['limit_date']					= get_emoney_limitdate('invite_to');
							$emoney['member_seq_to']		= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->emoney_insert($emoney, $memberseq);
						}

						if($app['pointInvitees']>0){
							unset($point);
							$point['type']								= 'invite_to';
							$point['point']								= $app['pointInvitees'];
							$point['gb']								= 'plus';
							$point['memo']							= '초대 포인트';
							$point['limit_date']						= get_point_limitdate('invite_to');
							$point['member_seq_to']			= $chk[0]['member_seq'];//2015-02-16
							$this->membermodel->point_insert($point, $memberseq);
						}
					}
				}

			}else{
				$this->db->where('member_seq', $params['member_seq']);
				$result = $this->db->update('fm_member', array("status"=>'hold'));
			}

			//신규회원가입쿠폰발급
			$this->load->model('couponmodel');
			$sc['whereis'] = ' and (type="member" or type="member_shipping")  and issue_stop != 1 ';//발급중지가 아닌경우
			$coupon_multi_list = $this->couponmodel->get_coupon_multi_list($sc);
			$coupon_multicnt = 0;
			foreach($coupon_multi_list as $coupon_multi){  $coupon_multicnt++;
				$this->couponmodel->_members_downlod( $coupon_multi['coupon_seq'], $memberseq);
			}
			if($coupon_multicnt) $coupon_msg ="<br/>회원가입 쿠폰이 발행 되었습니다."; 

			### LOG
			$qry = "update fm_member set login_cnt = login_cnt+1, lastlogin_date = now(), login_addr = '".$_SERVER['REMOTE_ADDR']."' where member_seq = '{$memberseq}'";
			$result = $this->db->query($qry);

			###
			$commonSmsData = array();
			$commonSmsData['join']['phone'][] = $params['cellphone'];
			$commonSmsData['join']['params'][] = $params;
			$commonSmsData['join']['mid'][] = $params['userid'];
			commonSendSMS($commonSmsData);
			sendMail($params['email'], 'join', $params['userid'], $params);

			$this->session->unset_userdata('fb_invite');//초대회원초기화
			return $result;
		}else{
			return false;
		}
	}


	/**
	* @ sns sns회원가입추가
	**/
	function Snsmemberck($params, $snstype = 'sns_f', $mbinfo = null ) {
		if(!$params[$snstype]) return '';
		$where_arr = array('sns_f' =>$params[$snstype], 'rute'=>$params['rute'] );
		$snsmbdata = get_data('fm_membersns', $where_arr);
		$snsmbparams = $snsmbdata[0];
		if($snsmbparams) {//있는 경우 업데이트
			$this->db->where(array('sns_f'=>$params[$snstype], 'rute'=>$params['rute']));
			if($params['member_seq']){
				$result = $this->db->update('fm_membersns', array("user_name"=>$params['user_name'],"email"=>$params['email'],"sex"=>$params['sex'],"birthday"=>$params['birthday'],"member_seq"=>$params['member_seq']));
			}else{
				$result = $this->db->update('fm_membersns', array("user_name"=>$params['user_name'],"email"=>$params['email'],"sex"=>$params['sex'],"birthday"=>$params['birthday'],"member_seq"=>$mbinfo['member_seq']));
			}
		}else{
			if($mbinfo['member_seq']){//회원계정통합시추가
				$params['member_seq'] = $mbinfo['member_seq'];
			}
			$params['sns_f'] = $params[$snstype];
			$data = filter_keys($params, $this->db->list_fields('fm_membersns'));
			$this->db->insert('fm_membersns', $data);
		}

		$memberseq = ($mbinfo['member_seq'])?$mbinfo['member_seq']:$params['member_seq'];
		$this->Snswinopenjoindb($memberseq);
	}

	/**
	* @ sns sns회원가입추가
	**/
	function Snswinopenjoindb($memberseq) {
		$snswhere_arr = array('session_id' =>$this->session->userdata('session_id'));
		$snsjoinmbdata = get_data('fm_membersns_join', $snswhere_arr);
		$snsjoinck = $snsjoinmbdata[0];
		if($snsjoinck) {//있는 경우 업데이트
			$this->db->where('session_id',$this->session->userdata('session_id'));
			$this->db->update('fm_membersns_join', array("member_seq"=>$memberseq,"session_id"=>$this->session->userdata('session_id'),"update_date"=>date('Y-m-d H:i:s')));
		}else{
			$this->db->delete('fm_membersns_join', array('member_seq' => $memberseq));
			$snsjoinparams['member_seq']	= $memberseq;
			$snsjoinparams['session_id']		= $this->session->userdata('session_id');
			$snsjoinparams['regist_date']		= date('Y-m-d H:i:s');
			$snsjoinparams['update_date']	= date('Y-m-d H:i:s');
			$data = filter_keys($snsjoinparams, $this->db->list_fields('fm_membersns_join'));
			$this->db->insert('fm_membersns_join', $data);
		}
	}

	/**
	*
	* @
	*/
	function sns_logout(){
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('fbuser');
		$this->session->unset_userdata('accesstoken');
		$this->session->unset_userdata('signedrequest');
		$this->session->unset_userdata('nvuser');
		$this->session->unset_userdata('mtype');
		$this->session->unset_userdata('naver_state');
		$this->session->unset_userdata('naver_access_token');
		$this->session->unset_userdata('kkouser');
		$this->session->unset_userdata('dmuser');
		$this->session->unset_userdata('daum_access_token');
		$this->session->unset_userdata('http_host');
		$this->session->unset_userdata('snslogn');
		$_SESSION['user']			= ''; $_SESSION['fbuser']				= '';
		$_SESSION['accesstoken']	= ''; $_SESSION['signedrequest']		= '';
		$_SESSION['nvuser']			= ''; $_SESSION['naver_access_token']	= '';
		$_SESSION['naver_state']	= ''; $_SESSION['mtype']				= '';
		$_SESSION['kkouser']		= ''; 
		$_SESSION['dmuser']			= ''; $_SESSION['daum_access_token']	= '';
		$_SESSION['http_host']		= ''; $_SESSION['snslogn']				= '';
		unset($this->userInfo, $_SESSION['user'], $_SESSION['fbuser'], $_SESSION['naver_state'], $_SESSION['naver_access_token'], $_SESSION['nvuser'], $_SESSION['accesstoken'], $_SESSION['signedrequest'],$_SESSION['kkouser'],$_SESSION['dmuser'],$_SESSION['daum_access_token']);

		pageReload('','parent');
		$_SESSION['user'] = '';
		exit;
	}

	/**
	* SNS 회원세션
	* @
	*/
	function create_member_session($data=array()){

		$this->load->helper('member');
		create_member_session($data);
		/**
		// 사업자 회원일 경우 업체명->이름
		if($data['business_seq']){
			$data['user_name'] = $data['bname'];
		}
		$member_data = array(
			'sns'.$data['rute'].'con'=> $data['sns_'.substr($data['rute'],0,1)],
			'member_seq'		=> $data['member_seq'],
			'userid'					=> $data['userid'],
			'user_name'			=> $data['user_name'],
			'birthday'				=> $data['birthday'],
			'sex'						=> $data['sex'],
			'rute'					=> substr($data['rute'],0,1)
		);
		$tmp = config_load('member');
		if(isset($tmp['sessLimit']) && $tmp['sessLimit']=='Y'){
			$limit = 60 * $tmp['sessLimitMin'];
			$this->session->sess_expiration = $limit;
		}

		$this->session->set_userdata(array('user'=>$member_data));
		**/
	}

	function snsredirecturl(){
		if( $_GET['snsloginstart'] ){
		$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
		$scripts[] = '<script type="text/javascript" src="/app/javascript/plugin/jquery.activity-indicator-1.0.0.min.js"></script>';
		$scripts[] = '<script type="text/javascript" src="/app/javascript/js/common.js"></script>';
		echo '<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko" >
<head>
<meta charset="utf-8"><link rel="stylesheet" type="text/css" href="/data/skin/'.$this->skin.'/css/common.css" />
<link rel="stylesheet" type="text/css" href="/data/skin/'.$this->skin.'/css/jqueryui/black-tie/jquery-ui-1.8.16.custom.css" />';
		foreach($scripts as $script){
			echo $script."\n";
		}
echo '<script type="text/javascript">	$(document).ready(function() {
	loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: \'#000000\', speed: 1.5});
});
</script>
</head><body>
</body>
<div id="openDialogLayer" style="display: none">
<div align="center" id="openDialogLayerMsg"></div>
</div>
<div id="ajaxLoadingLayer" style="display: none"></div>
</html>
		';
		}else{

			$snsurlar		= explode("?",$_GET[snsurl]);
			parse_str($snsurlar[1],$snsurlparam);

			$snsdataform	= "";
			foreach($snsurlparam as $snsname => $snsvalue){
				$snsdataform .= '<input type="hidden" name="'.$snsname.'" value="'.$snsvalue.'" >';
			}

			/*
			foreach($snsurlparam as $snsurl){
				$snsinput = explode("=",$snsurl);
				$snsdataform .= '<input type="hidden" name="'.$snsinput[0].'" value="'.$snsinput[1].'" >';
			}
			*/
			$scripts[] = "<script type='text/javascript' src='/app/javascript/jquery/jquery.min.js'></script>";
			$scripts[] = "<script type='text/javascript'>";
			$scripts[] = "$(function() {";
			$scripts[] = 'document.form_chk.submit();';
			$scripts[] = "});";
			$scripts[] = "</script>";
			echo '<html><head>';
			foreach($scripts as $script){
				echo $script."\n";
			}
			echo '</head><body>
			<form  name="form_chk" action="'.$snsurlar[0].'">
			'.$snsdataform.'
			</form>
			</body>
			</html>
			';
		}
		exit;
	}

	//goods>view : interests/write/buy
	public function fbopengraph()
	{
		//$this->snssocial->facebooklogin();
		$fbuserprofile = $this->snssocial->facebookuserid();
		if ( !$fbuserprofile ) {
			$this->facebook = new Facebook(array(
			  'appId'  => $this->__APP_ID__,
			  'secret' => $this->__APP_SECRET__,
			  "cookie" => true
			));
			// Get User ID
			$fbuserprofile = $this->facebook->getUser();
			if($fbuserprofile && !$this->session->userdata('fbuser')){
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}else{
				$fbuserprofile = $this->snssocial->facebooklogin();
				if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}
		}else{
			if( !$this->session->userdata('fbuser') ) {
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}
		}

		$no		= ($_POST['no'])?(int) $_POST['no']:(int) $_GET['no'];
		$id		= ($_POST['id'])?$_POST['id']:$_GET['id'];
		$type	= ($_POST['type'])?$_POST['type']:$_GET['type'];
		if($this->session->userdata('fbuser')) {//페이스북회원인경우
			/**
			* facebook opengraph > love item
			**/
			if($no){
				$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
				if( !$fbpermissions['error'] &&  !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) ) && $type=='interests' ) {
					if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ){
						echo("window.open('/member/register_sns_form?popup=1&formtype=wishadd&firstmallcartid={$this->session->userdata(session_id)}&snstype=facebook&stream=publish_actions&snsreferer={$_SERVER[HTTP_HOST]}&return_url={$_SERVER[HTTP_REFERER]}','snspopup','width=800px,height=400px,statusbar=no,scrollbars=no,toolbar=no');");
						exit;
					}else{
						echo("window.open('http://{$this->config_system[subDomain]}/member/register_sns_form?popup=1&formtype=wishadd&firstmallcartid={$this->session->userdata(session_id)}&snstype=facebook&stream=publish_actions&snsreferer={$_SERVER[HTTP_HOST]}&return_url={$_SERVER[HTTP_REFERER]}','snspopup','width=800px,height=400px,statusbar=no,scrollbars=no,toolbar=no');");
						exit;
					}
				}else{
					if($type=='write'){//게시글 등록시 게시글상세페이지로 이동
						if ( empty($id) ) $id = 'goods_review';
						$product_url = $this->domainurl.'/board/view?id='.$id.'&seq='.$no;
					}else{
						$product_url = $this->domainurl.'/goods/view?no='.$no;
					}
					$objectid = $this->goodsview_opengraph($product_url, $type);
					exit;
				}
			}
		}
		exit;
	}

	//board>write
	public function fbmefeed()
	{
		//$this->snssocial->facebooklogin();
		$fbuserprofile = $this->snssocial->facebookuserid();
		if ( !$fbuserprofile ) {
			$this->facebook = new Facebook(array(
			  'appId'  => $this->__APP_ID__,
			  'secret' => $this->__APP_SECRET__,
			  "cookie" => true
			));
			// Get User ID
			$fbuserprofile = $this->facebook->getUser();
			if($fbuserprofile && !$this->session->userdata('fbuser')){
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}else{
				$fbuserprofile = $this->snssocial->facebooklogin();
				if( $fbuserprofile && !$this->session->userdata('fbuser') ) {
					$this->session->set_userdata('fbuser', $fbuserprofile);
				}
			}
		}else{
			if( !$this->session->userdata('fbuser') ) {
				$this->session->set_userdata('fbuser', $fbuserprofile);
			}
		}

		$no		= ($_POST['no'])?(int) $_POST['no']:(int) $_GET['no'];
		$id		= ($_POST['id'])?$_POST['id']:$_GET['id'];
		$type	= ($_POST['type'])?$_POST['type']:$_GET['type'];

		if ( empty($type) ) $type = 'board';
		if($this->session->userdata('fbuser')) {//페이스북회원인경우
			/**
			* facebook opengraph > love item
			**/
			if($no){
				$fbpermissions = $this->snssocial->facebookpermissions($this->snssocial->facebook);
				if( !$fbpermissions['error'] && !(array_key_exists('publish_actions', $fbpermissions['data'][0]) || in_array('publish_actions', $fbpermissions) )  ) {
					if( $this->__APP_DOMAIN__ == $_SERVER['HTTP_HOST'] ){
						echo("window.open('/member/register_sns_form?popup=1&formtype=wishadd&firstmallcartid={$this->session->userdata(session_id)}&snstype=facebook&stream=publish_actions&snsreferer={$_SERVER[HTTP_HOST]}&return_url={$_SERVER[HTTP_REFERER]}','snspopup','width=800px,height=400px,statusbar=no,scrollbars=no,toolbar=no');");
						exit;
					}else{
						echo("window.open('http://{$this->config_system[subDomain]}/member/register_sns_form?popup=1&formtype=wishadd&firstmallcartid={$this->session->userdata(session_id)}&snstype=facebook&stream=publish_actions&snsreferer={$_SERVER[HTTP_HOST]}&return_url={$_SERVER[HTTP_REFERER]}','snspopup','width=800px,height=400px,statusbar=no,scrollbars=no,toolbar=no');");
						exit;
					}
				}else{
					if($type=='board'){//게시글 등록시 게시글상세페이지로 이동
						if ( empty($id) ) $id = 'goods_review';
						if( $id == 'goods_qna' ) {
							$this->load->model('Goodsqna','Boardmodel');
						}elseif( $id == 'goods_review' ) {
							$this->load->model('Goodsreview','Boardmodel');
						}elseif( $id == 'bulkorder' ) {//대량구매게시판
							$this->load->model('Boardbulkorder','Boardmodel');
						}else{
							$this->load->model('Boardmodel');
						}

						$parentsql['whereis']	= ' and seq= "'.$no.'" ';
						$parentsql['select']		= ' subject, contents, name ';
						$parentdata = $this->Boardmodel->get_data($parentsql);//게시판목록

						$link = $this->domainurl.'/board/view?id='.$id.'&seq='.$no;
						$message		= strip_tags($parentdata['subject']);
						$name				= $parentdata['name'];
						$data = array('message'=>$parentdata['name'],'name'=>$name,'link'=> $link);
						$objectid = $this->snssocial->facebook_mefeed($data);
						if( !$objectid['error'] ){
							//$_POST['seq'] = $no;
							$upparams['sns_fb_feedid']  = $objectid;
							//$this->Boardmodel->data_modify($upparams);
						}
					}else{//그외에 것
						$link = $this->domainurl.'/board/view?id='.$id.'&seq='.$no;//link url
						$message		= "message";//message
						$name				= "name";//name
						$data = array('message'=>$message,'name'=>$name,'link'=> $link);
						$objectid = $this->snssocial->facebook_mefeed($data);
						if( !$objectid['error'] ){
							//$_POST['seq'] = $no;
							$upparams['sns_fb_feedid']  = $objectid;
							//$this->Boardmodel->data_modify($upparams);
						}
					}
					exit;
				}
			}
		}
		exit;
	}

}

/* End of file sns_process.php */
/* Location: ./app/controllers/sns_process.php */