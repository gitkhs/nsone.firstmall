<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/admin_base".EXT);

# 개인 맞춤형 알림
# 각 상황에 따라 발송 일자가 맞는지 확인.
# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기


class reservation extends admin_base {

	public function __construct(){
		parent::__construct();
		$this->load->helper('reservation');

		//debug($_COOKIE);

	}

	public function index()
	{
		redirect("/reservation/coupon");
	}

	## 이번주 만료 쿠폰 안내 : 매주 월요일 관리자 {지정시간} 발송
	public function coupon(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴

		personal_review_menu();

		if($personal_use['personal_coupon_use'] == 'y'|| $_GET['admin'] == 'y'){
			# 오늘이 월요일 일때.
			if(date("w",mktime()) == 1 || $_GET['admin'] == 'y'){
				$res = send_reserv_coupon($emode,$smode,$logview);
				debug($res);
			}else{
				$str[] = "이번주 만료될 쿠폰 알림 : 매주 월요일 발송 (오늘은 월요일이 아닙니다.)";
			}
		}else{
			$str[] = "이번주 만료될 쿠폰 알림 : 사용안함";
		}

		personal_review_msg('coupon',$str);
	}

	## 다음달 소멸 적립금 안내 : 전월 {지정날짜} , {지정시간}에 발송
	public function emoney(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴
		
		personal_review_menu();
		//error_reporting(E_ALL);

		if($personal_use['personal_emoney_use'] == 'y' || $_GET['admin'] == 'y'){

			## 지정 예약일
			$sms_personal		= config_load('sms_personal');
			$reserve_day		= $sms_personal['personal_emoney_day'];

			# 오늘이 지정 예약일 일때.
			if(date("d",mktime()) == $reserve_day || $_GET['admin'] == 'y'){
				send_reserv_emoney($emode,$smode,$logview);
			}else{
				$str[] = "오늘(".date("d",mktime()).")은 지정예약일이 아닙니다.<br />지정예약일은 ".$reserve_day."일 입니다.";
			}
		}else{
			$str[] = "다음달 소멸 적립금 알림 : 사용안함";
		}

		personal_review_msg('emoney',$str);
	}

	## 회원등급 혜택 안내
	public function membership(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴

		personal_review_menu();

		if($personal_use['personal_membership_use'] == 'y' || $_GET['admin'] == 'y'){

			## 등급조정일 => 회원별 실제등급조정일 기준
			//$grade_clone	= config_load('grade_clone');
			//$chg_day		= $grade_clone['chg_day'];

			## 지정 예약일 : 조정일 +{지정일수}
			//$sms_personal	= config_load('sms_personal');
			//$after_day		= $sms_personal['personal_membership_day'];
			//$reserve_day	= $chg_day + $after_day;

			# 오늘이 지정 예약일 일때.
			//if($reserve_day == date("d",mktime()) || $_GET['admin'] == 'y'){
			send_reserv_membership($emode,$smode,$logview);
			//}else{
			//	$str[] = "오늘(".date("d",mktime())."일)은 지정예약일이 아닙니다.<br />";
			//	$str[] = "지정예약일은 ".$reserve_day."일(등급조정일 ".$chg_day."일 +".$after_day."일) 입니다.";
			//}
		}else{
			$str[] = "회원등급혜택 알림 : 사용안함";
		}

		personal_review_msg('membership',$str);
	}

	## 어제담은 장바구니/위시리스트 안내
	public function cart(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		## 지정 예약일 : 없음. 어제 장바구니/위시리스트 조회해서 발송.
		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴

		personal_review_menu();

		if($personal_use['personal_emoney_use'] == 'y' || $_GET['admin'] == 'y'){

			# 오늘이 지정 예약일 일때.
			$str[] = "장바구니/위시리스트 상품을 안내합니다.";
			send_reserv_cart($emode,$smode,$logview);

		}else{
			$str[] = "장바구니/위시리스트 알림 : 사용안함";
		}

		personal_review_msg('cart',$str);
	}

	## 장바구니/위시리스트에 담긴 타임세일 종료 상품 안내
	public function timesale(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴

		personal_review_menu();

		if($personal_use['personal_timesale_use'] == 'y' || $_GET['admin'] == 'y'){

			send_reserv_timesale($emode,$smode,$logview);

		}else{
			$str[] = "장바구니/위시리스트 타임세일 알림 : 사용안함";
		}
		personal_review_msg('timesale',$str);
	}

	## 배송완료고객 배송완료일 +{지정일수} 상품 리뷰 작성 안내
	public function review(){

		# mode 값 : view(내용확인), send(발송), logview = y 일때 로그보이기
		$emode		= $_GET['emode'];
		$smode		= $_GET['smode'];
		$logview	= $_GET['logview'];

		$personal_use		= config_load('personal_use');		//알림 사용여부 불러옴

		personal_review_menu();

		if($personal_use['personal_review_use'] == 'y' || $_GET['admin'] == 'y'){
			send_reserv_review($emode,$smode,$logview);
		}else{
			$str[] = "배송완료고객 배송완료일 +{지정일수} 상품 리뷰 작성안내 : 사용안함";
		}
		personal_review_msg('review',$str);

	}

}

?>