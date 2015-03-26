
===================================================================
 All@Pay
  - http://www.allatpay.com
  - AllatPayBasic_PHP V1.0.5.0

 (c) 2007 Allat Corp. All rights reserved.
====================================================================


파일 설명 :
===========
1. 서버 Util
    - allatutil.php : 서버에 upload 함

2. 결제 승인예제
    - approval.html : 올앳결제창 호출 예제 페이지
    - allat_approval.php : 올앳에 승인요청하는 예제 페이지

3. 결제 취소예제
    - cancel.html : 취소를 하기위한 Input 값 예제 페이지
    - allat_cancel.php : 올앳에 취소요청하는 예제 페이지

4. 결제 매입예제 (자동매입인 경우 매입요청이 따로 필요 없음)
    - sanction.html : 매입을 하기위한 Input 값 예제 페이지
    - allat_sanction.php : 올앳에 매입요청하는 예제 페이지

5. 현금영수증 승인(발급)예제
    - cashapproval.html : 현금영수증 승인(발급)을 하기 위한 Input 값 예제 페이지
    - allat_cashapproval.php : 올앳에 현금영수증 승인(발급)요청하는 예제 페이지

6. 현금영수증 취소예제
    - cashcancel.html : 현금영수증 취소을 하기 위한 Input 값 예제 페이지
    - allat_cashcancel.php : 올앳에 현금영수증 취소요청하는 예제 페이지

7. 현금영수증 사업자등록예제 -> 올앳 운영자에서 현금영수증 사업자 등록하기 때문에 사용할 필요 없음
    - cashregistry.html : 현금영수증 사업자 등록을 위한 Input 값 예제 페이지
    - allat_cashregistry.php : 올앳에 현금영수증 사업자 등록을 위한 예제 페이지

8. SSL 통신 테스트 프로그램
    - ssltest.php : ssl 통신이 가능한지 test 하는 프로그램
                    이 프로그램에서 실패한 경우 php 와 openssl 이 연동이 안되어 있는 경우이므로,
                    승인/매입/취소 요청시 "SSL" 옵션 대신 "NOSSL" 옵션으로 요청함

9. 에스크로 배송일 개시예제 -> 에스크로 적용 승인건은 배송을 개시을 하여야 함
    - escrowcheck.html : 에스크로건에 대한 배송등록을 하기 위한 Input 값 예제 페이지
    - allat_escrowcheck.php : 올앳에 배송등록요청하는 예제 페이지

10. 에스크로 반품등록 예제 -> 에스크로 적용 건에 대한 반품 등록을 요청할때.
    - escrowreturn.html : 에스크로건에 대한 반품등록을 하기 위한 Input 값 예제 페이지
    - allat_escrowreturn.php : 올앳에 반품등록을 요청하는 예제 페이지