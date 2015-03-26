<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class personal_referer extends front_base {

	public function access()
	{

		$_GET['param']	= str_replace(" ","+",$_GET['param']);
		$param			= unserialize(base64_decode($_GET['param']));

		## 정상적인 접근일때만
		if($param['inflow'] != "shorturl"){
			
			$nowdt		= date("Y-m-d H:i:s",mktime());
			$senddt		= $param['send_date'];

			$datetime1	= strtotime($nowdt);
			$datetime2	= strtotime($senddt);
			$interval	= $datetime1 - $datetime2;
	
			if($senddt && $interval > 60){

				$this->load->helper('reservation');

				## 접속환경
				$access_type = access_config();

				### 고객 리마인드 서비스 관련 테이블
				### fm_log_curation : 유입로그
				### fm_log_curation_info : 유입후 활동 로그
				### fm_log_curation_sms : sms 발송로그
				### fm_log_curation_email : email 발송로그
				### fm_log_curation_summary : 발송/유입통계데이터

				### 고객 리마인드 서비스 발송 구분
				/*
				$personal_gubun	= array('coupon'	=>'이번주 만료될 할인쿠폰',
										'emoney'	=>'적립금',
										'membership'=>'멤버쉽서비스',
										'cart'		=>'장바구니/위시리스트',
										'timesale'	=>'판매종료',
										'review'	=>'상품리뷰',
										'wish'		=>'위시리스트'
									);
				*/
				if($param['inflow'] == "email_timesale_cart" || $param['inflow'] == "email_timesale_wish"){
					$inflow = "email_timesale";
				}else{
					$inflow		= $param['inflow'];
				}
				$inflow_tmp		= explode("_",$inflow);
				if(in_array($inflow_tmp[0],array("email","sms"))){

					$inflow_type	= strtoupper($inflow_tmp[0]);
					$inflow_kind	= $inflow_tmp[1];
					$personal_gubun = curation_menu();
					foreach($personal_gubun as $k=>$v){
						if($inflow_kind == "wish"){
							$inflow_kind = "cart";
						}
						if(strstr($v['name'],$inflow_kind)){
							$personal_title = $v['title'];
						}
					}
					$inflow_subject	= $personal_title;
					//$click_zone		= mypage_url($param['inflow'],$param['member_seq']);
					$senddtsearch	= date("Y-m-d",$datetime2);

					## 유입 로그.
					$sql = "select 
								count(*) cnt
							from 
								fm_log_curation 
							where 
								member_seq='".$param['member_seq']."' 
								and inflow_kind='".$inflow_kind."' 
								and inflow_type='".$inflow_type."'
								and send_date='".date("Y-m-d",mktime())."'
							";
					$query	= $this->db->query($sql);
					$result = $query->row_array();
					$curation_cnt = $result['cnt'];
			
					$sql = "select 
								curation_seq
							from 
								fm_log_curation 
							where 
								member_seq='".$param['member_seq']."' 
								and inflow_kind='".$inflow_kind."' 
								and inflow_type='".$inflow_type."'
								and access_type='".$access_type."'
								and send_date between '".$senddtsearch." 00:00:00' and '".$senddtsearch." 23:59:59'
							";
					$query	= $this->db->query($sql);
					$result = $query->row_array();
					$curation_seq = $result['curation_seq'];

					## 문자발송건이 pc로 접속했을때 => 비정상접근으로 체크
					if($inflow_type=="SMS" && $access_type == "PC"){
						$except_use = "y";
					}
					## 유입로그 저장
					if(!$curation_seq && !$curation_cnt && $except_use != "y"){

						$sql = "select count(*) cnt from fm_log_curation where member_seq='".$param['member_seq']."' and inflow_kind='".$inflow_kind."' and inflow_type='".$inflow_type."' and send_date between '".$senddtsearch." 00:00:00' and '".$senddtsearch." 23:59:59'";
						$query	= $this->db->query($sql);
						$result = $query->row_array();

						$summary_cnt = $result['cnt'];
						if(!$summary_cnt) $summary_cnt = 0;

						$sql = "select userid from fm_member where member_seq='".$param['member_seq']."'";
						$que = $this->db->query($sql);
						$res = $que->row_array();
						$userid = $res['userid'];

						$sql = "select to_mobile,sms_msg from fm_log_curation_sms where member_seq='".$param['member_seq']."' and kind='".$inflow_kind."' and regist_date between '".$senddtsearch." 00:00:00' and '".$senddtsearch." 23:59:59'";
						$que = $this->db->query($sql);
						$res = $que->row_array();
						$to_reception	= $res['to_mobile'];
						$to_msg			= $res['sms_msg'];

						$params = array();
						$params['member_seq']	= $param['member_seq'];
						$params['userid']		= $userid;
						$params['to_reception'] = $to_reception;
						$params['to_msg']		= $to_msg;
						$params['send_date']	= $param['send_date'];
						$params['inflow_kind'] = $inflow_kind;
						$params['inflow_subject']= $inflow_subject;
						$params['inflow_type']	= $inflow_type;
						$params['access_type']	= $access_type;
						$params['click_zone']	= $click_zone;
						$params['referer']		= $_SERVER['HTTP_REFERER'];
						$params['regist_date']= date("Y-m-d H:i:s",mktime());

						$result			= $this->db->insert('fm_log_curation', $params);
						$curation_seq	= $this->db->insert_id();
						### 발송 통계
						if($summary_cnt == 0){
							$sql = "select count(*) as cnt from fm_log_curation_summary where inflow_kind='".$inflow_kind."' and send_date ='".$senddtsearch."'";
							$query	= $this->db->query($sql);
							$res	= $query->row_array();
							if($res['cnt'] > 0){
								$que = "update fm_log_curation_summary set inflow_".$inflow_tmp[0]."_total=inflow_".$inflow_tmp[0]."_total+1 where inflow_kind='".$inflow_kind."' and send_date ='".$senddtsearch."'";
							}else{
								$que = "insert into fm_log_curation_summary set inflow_kind='".$inflow_kind."',inflow_".$inflow_tmp[0]."_total=1,send_date ='".$senddtsearch."'";
							}
							$this->db->query($que);
						}
					}
					
					## curation session 생성
					$curation = array();
					$curation['member_seq']		= $param['member_seq'];
					$curation['inflow']			= $inflow;
					$curation['curation_seq']	= $curation_seq;

					## Cookie생성 : 접속일 + 2일 자정전까지 유지
					$mktime			= strtotime(date("Y-m-d H:i:s")." +2 days");
					$dt				= date("Y-m-d 23:59:59",$mktime);
					$curationtmp	= implode("^",$curation);
					setcookie("curation",$curationtmp,$mktime,'/');
					$_COOKIE["curation"] = $curationtmp;

					if($param['inflow'] == "email_timesale_cart" || $param['inflow'] == "email_timesale_wish"){
						$inflow_kind = $inflow_tmp[2];
					}

					## 이동 페이지 지정
					switch(strtolower($inflow_kind)){
						case "coupon":		$redirect_url = "/mypage/coupon"; break;
						case "emoney":		$redirect_url = "/mypage/cash"; break;
						case "membership":	$redirect_url = "/mypage"; break;
						case "cart":		$redirect_url = "/order/cart"; break;
						case "wish":		$redirect_url = "/mypage/wish"; break;
						case "review":		$redirect_url = "/mypage/mygdreview_catalog"; break;
						default: $redirect_url = "/index"; break;
					}
					pageRedirect($redirect_url,'','parent');
					
				}else{
					pageRedirect('/mypage','','parent');
				}
			}else{
				pageRedirect('/mypage','','parent');
			}

		}else{
			pageRedirect('/mypage','','parent');
		}
	}
}

