<?

if( !function_exists( "bcdiv" ) )
{
    function truncate1 ($num, $digits = 0) {
        $shift = pow(10 , $digits);
        return ((floor($num * $shift)) / $shift);
    }

    function bcdiv($_ro, $_lo, $_scale=0)
    {
        return truncate1($_ro / $_lo, $_scale);
    }

    function bcadd($_ro, $_lo, $_scale=0)
    {
        return truncate1($_ro + $_lo, $_scale);
    }

    function bcmul($_ro, $_lo, $_scale=0)
    {
        return truncate1($_ro * $_lo, $_scale);
    }

    function bcpow($num, $power) {
        $awnser = "1";
	
        while ($power) {
            $awnser = bcmul($awnser, $num, 100);
            $power = bcsub($power, "1");
        }
        return rtrim($awnser, '0.');
    }

    /**
     * my_bcmod - get modulus (substitute for bcmod)
     * string my_bcmod ( string left_operand, int modulus )
     * left_operand can be really big, but be carefull with modulus :(
     * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania
     **/
    function bcmod( $x, $y )
    {
        $oldx = $x;
        // how many numbers to take at once? carefull not to exceed (int)
        $take = 5;
        $mod = '';

        do
        {
            $a = (int)$mod.substr( $x, 0, $take );
            $x = substr( $x, $take );
            $mod = $a % $y;
        }
        while ( strlen($x) );

        return (int)$mod;
    }

    function bcsub($num1, $num2){
        return $num1-$num2;
    }

    function bccomp($Num1,$Num2,$Scale=null) {
        // check if they're valid positive numbers, extract the whole numbers and decimals
        if(!preg_match("/^\+?(\d+)(\.\d+)?$/",$Num1,$Tmp1)||
            !preg_match("/^\+?(\d+)(\.\d+)?$/",$Num2,$Tmp2)) return('0');

        // remove leading zeroes from whole numbers
        $Num1=ltrim($Tmp1[1],'0');
        $Num2=ltrim($Tmp2[1],'0');

        // first, we can just check the lengths of the numbers, this can help save processing time
        // if $Num1 is longer than $Num2, return 1.. vice versa with the next step.
        if(strlen($Num1)>strlen($Num2)) return(1);
        else {
            if(strlen($Num1)<strlen($Num2)) return(-1);

            // if the two numbers are of equal length, we check digit-by-digit
            else {

                // remove ending zeroes from decimals and remove point
                $Dec1=isset($Tmp1[2])?rtrim(substr($Tmp1[2],1),'0'):'';
                $Dec2=isset($Tmp2[2])?rtrim(substr($Tmp2[2],1),'0'):'';

                // if the user defined $Scale, then make sure we use that only
                if($Scale!=null) {
                    $Dec1=substr($Dec1,0,$Scale);
                    $Dec2=substr($Dec2,0,$Scale);
                }

                // calculate the longest length of decimals
                $DLen=max(strlen($Dec1),strlen($Dec2));

                // append the padded decimals onto the end of the whole numbers
                $Num1.=str_pad($Dec1,$DLen,'0');
                $Num2.=str_pad($Dec2,$DLen,'0');

                // check digit-by-digit, if they have a difference, return 1 or -1 (greater/lower than)
                for($i=0;$i<strlen($Num1);$i++) {
                    if((int)$Num1{$i}>(int)$Num2{$i}) return(1);
                    else
                        if((int)$Num1{$i}<(int)$Num2{$i}) return(-1);
                }

                // if the two numbers have no difference (they're the same).. return 0
                return(0);
            }
        }
    }
}


function TraceLog ($strLogMsg)
{
	$LOG_PATH	= "pg/kspay/log/";
	$curr_time_14_fmt = strftime("%Y%m%d%H:%M:%S");
	
	$strLogFile =	$LOG_PATH. substr($curr_time_14_fmt,0,8) .".txt";
	
	$strRecord = "[" . substr($curr_time_14_fmt,8) . "]" . $strLogMsg . "\n";

	$fp	= fopen($strLogFile, "a");
	fwrite($fp,	$strRecord);
	fclose($fp);
}

class KSPayEncApprovalCancel
{
	var $IPAddr  ;
	var $Port    ;

	var $SendHeadMsg        ;
	var $SendDataMsg        ;

	var $ReceiveHeadMsg     ;
	var $ReceiveDataMsg     ;

	var $SendCount    = 0;
	var $ReceiveCount = 0;

	// Haeder
	var $EncType            ;// 0: 암화안함, 2: seed
	var $Version            ;// 전문버전
	var $Type               ;// 구분
	var $Resend             ;// 전송구분 : 0 : 처음,  1: 재전송
	var $RequestDate        ;// 요청일자 : yyyymmddhhmmss
	var $StoreId            ;// 상점아이디
	var $OrderNumber        ;// 주문번호
	var $UserName           ;// 주문자명
	var $IdNum              ;// 주민번호 or 사업자번호
	var $Email              ;// email
	var $GoodType           ;// 제품구분 1 : 실물, 2 : 디지털
	var $GoodName           ;// 제품명
	var $KeyInType          ;// KeyInType 여부 : S : Swap, K: KeyInType
	var $LineType           ;// lineType 0 : offline, 1:internet, 2:Mobile
	var $PhoneNo            ;// 휴대폰번호
	var $ApprovalCount      ;// 복합승인갯수
	var $HeadFiller         ;// 예비

	// 신용카드승인결과
	var $ApprovalType           ;// 승인구분
	var $TransactionNo          ;// 거래번호
	var $Status                 ;// 상태 O : 승인 , X : 거절
	var $TradeDate              ;// 거래일자
	var $TradeTime              ;// 거래시간
	var $IssCode                ;// 발급사코드
	var $AquCode                ;// 매입사코드
	var $AuthNo                 ;// 승인번호 or 거절시 오류코드
	var $Message1               ;// 메시지1
	var $Message2               ;// 메시지2
	var $CardNo                 ;// 카드번호
	var $ExpDate                ;// 유효기간
	var $Installment            ;// 할부
	var $Amount                 ;// 금액
	var $MerchantNo             ;// 가맹점번호
	var $AuthSendType           ;// 전송구분
	var $ApprovalSendType       ;// 전송구분(0 : 거절, 1 : 승인, 2: 원카드)
	var $Point1                 ;//
	var $Point2                 ;//
	var $Point3                 ;//
	var $Point4                 ;//
	var $VanTransactionNo       ;// Van 거래번호
	var $Filler                 ;// 예비
	var $AuthType               ;// ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
	var $MPIPositionType        ;// K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
	var $MPIReUseType           ;// Y : 재사용, N : 재사용아님
	var $EncData                ;// MPI, ISP 데이터

	// 가상계좌승인결과
	var $VATransactionNo        ;// 거래번호
	var $VAStatus               ;// 상태 O : 승인 , X : 거절
	var $VATradeDate            ;// 거래일자
	var $VATradeTime            ;// 거래시간
	var $VABankCode             ;// 은행코드
	var $VAVirAcctNo            ;// 가상계좌번호
	var $VAName                 ;// 예금주
	var $VACloseDate            ;// 은행일
	var $VACloseTime            ;// 은행시간
	var $VARespCode             ;// 응답코드
	var $VAMessage1             ;// 메세지1
	var $VAMessage2             ;// 메세지2
	var $VAFiller               ;// 예비

	// 계좌이체승인결과
	var $ACTransactionNo        ;// 거래번호
	var $ACStatus               ;// 오류구분 :승인 X:거절
	var $ACTradeDate            ;// 거래 개시 일자(YYYYMMDD)
	var $ACTradeTime            ;// 거래 개시 시간(HHMMSS)
	var $ACAcctSele             ;// 계좌이체 구분 - 2:PopBanking,4:새마을금고,5:금결원,6:PopBanking(휴대폰인증),8:CMA계좌이체
	var $ACFeeSele              ;// 선/후불제구분 - 2:후불
	var $ACInjaName             ;// 인자명(통장인쇄메세지-상점명)
	var $ACPareBankCode         ;// 입금모계좌코드
	var $ACPareAcctNo           ;// 입금모계좌번호
	var $ACCustBankCode         ;// 출금모계좌코드
	var $ACCustAcctNo           ;// 출금모계좌번호
	var $ACAmount               ;// 금액    (결제대상금액)
	var $ACBankTransactionNo    ;// 은행거래번호
	var $ACIpgumNm              ;// 입금자명
	var $ACBankFee              ;// 계좌이체 수수료
	var $ACBankAmount           ;// 총결제금액(결제대상금액+ 수수료
	var $ACBankRespCode         ;// 오류코드
	var $ACMessage1             ;// 오류 message 1
	var $ACMessage2             ;// 오류 message 2
	var $ACEntrNumb             ;// 사업자번호
	var $ACShopPhone            ;// 전화번호
	var $ACCavvSele             ;//
	var $ACFiller               ;//
	var $ACEncData              ;//

	// 월드패스승인결과
	var $WPTransactionNo        ;
	var $WPStatus               ;
	var $WPTradeDate            ;
	var $WPTradeTime            ;
	var $WPIssCode              ;//발급사코드
	var $WPAuthNo               ;//승인번호
	var $WPBalanceAmount        ;//잔액
	var $WPLimitAmount          ;//한도액
	var $WPMessage1             ;//메시지1
	var $WPMessage2             ;//메시지2
	var $WPCardNo               ;//카드번호
	var $WPAmount               ;//금액
	var $WPMerchantNo           ;//가맹점번호
	var $WPFiller               ;

	// 포인트카드승인결과
	var $PTransactionNo         ;// 거래번호
	var $PStatus                ;// 상태 O : 승인 , X : 거절
	var $PTradeDate             ;// 거래일자
	var $PTradeTime             ;// 거래시간
	var $PIssCode               ;// 발급사코드
	var $PAuthNo                ;// 승인번호 or 거절시 오류코드
	var $PMessage1              ;// 메시지1
	var $PMessage2              ;// 메시지2
	var $PPoint1                ;// 거래포인트
	var $PPoint2                ;// 가용포인트
	var $PPoint3                ;// 누적포인트
	var $PPoint4                ;// 가맹점포인트
	var $PMerchantNo            ;// 가맹점번호
	var $PNotice1               ;//
	var $PNotice2               ;//
	var $PNotice3               ;//
	var $PNotice4               ;//
	var $PFiller                ;// 예비

	// 현금영수증승인결과
	var $HTransactionNo         ;// 거래번호
	var $HStatus                ;// 오류구분 O:정상 X:거절
	var $HCashTransactionNo     ;// 현금영수증 거래번호
	var $HIncomeType            ;// 0: 소득      1: 비소득
	var $HTradeDate             ;// 거래 개시 일자
	var $HTradeTime             ;// 거래 개시 시간
	var $HMessage1              ;// 응답 message1
	var $HMessage2              ;// 응답 message2
	var $HCashMessage1          ;// 국세청 메시지 1
	var $HCashMessage2          ;// 국세청 메시지 2
	var $HFiller                ;// 예비

	// 상품권 온라인 PIN 발급 결과
	var $STTransactionNo        ;// 거래번호
	var $STStatus               ;// 오류구분 - O:성공  X:실패 S : 확인필요
	var $STTradeDate            ;// 거래일자
	var $STTradeTime            ;// 거래시간
	var $STGoveSele             ;// 기관구분
	var $STPinType              ;// 문화 M / 게임문화 G
	var $STAuthNo               ;// 승인번호 - 오류시 오류코드
	var $STRespMsg              ;// 메시지
	var $STAmount               ;// 결제금액
	var $STPinNumb              ;// PIN 번호
	var $STCertNo               ;// 관리번호
	var $STExpDate              ;// 유효기간
	var $STFiller               ;// 예비

	//생성자
	public function __construct($_ip, $_port)
	{
		$this->IPAddr  = $_ip;
		$this->Port    = $_port;
	}

	/*****************************************************************************/
	/* 자리수 맞추는 함수                                  */
	/* $TSTR : 입력 스트링                             */
	/* $TLEN : 길이                                */
	/* $TAG  : 스페이스로 대치 할건지 (프로그램시 화면에서 단지 보기위함 )     */
	/*****************************************************************************/
	function fmt($src, $len, $type)
	{
		$rmsg = "";
		$ptoken = " ";
		if ('9' == $type) $ptoken = "0";

		$midx   = 0;
		$tlen   = 0;

		if (isset($src))
		{
			$rsrc = stripslashes($src);
			$slen = strlen($rsrc) ;

			for($i=0 ; $i< $slen ;)
			{
				$tlen = 1;
				if (127 < ord(substr($rsrc,$i,1))) $tlen = 2;
				
				 $i += $tlen;

				if ($midx + $tlen > $len) break;
				$midx += $tlen;
			}

			$rmsg = substr($rsrc, 0, $midx);
		}

		if ($midx < $len)
		{
			for($i=$midx ; $i< $len ; $i++)
			{
				if ('9' == $type)
				{
					$rmsg = $ptoken . $rmsg;
				}else
				{
					$rmsg = $rmsg . $ptoken;
				}
			}
		}

		return $rmsg;
	}

	function HeadMessage
	(
		$_pEncType           ,// 0: 암화안함, 2: seed
		$_pVersion           ,// 전문버전
		$_pType              ,// 구분
		$_pResend            ,// 전송구분 : 0 : 처음,  1: 재전송
		$_pRequestDate       ,// 요청일자 : yyyymmddhhmmss
		$_pStoreId           ,// 상점아이디
		$_pOrderNumber       ,// 주문번호
		$_pUserName          ,// 주문자명
		$_pIdNum             ,// 주민번호 or 사업자번호
		$_pEmail             ,// email
		$_pGoodType          ,// 제품구분 0 : 실물, 1 : 디지털
		$_pGoodName          ,// 제품명
		$_pKeyInType         ,// KeyInType 여부 : S : Swap, K: KeyInType
		$_pLineType          ,// lineType 0 : offline, 1:internet, 2:Mobile
		$_pPhoneNo           ,// 휴대폰번호
		$_pApprovalCount     ,// 복합승인갯수
		$_pFiller            )// 예비
	{
		$TmpHeadMsg  = "";
		$TmpHeadMsg .= "    ";//길이는 나중에 채우자

		$TmpHeadMsg .= $this->fmt($_pEncType            ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pVersion            ,4  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pType               ,2  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pResend             ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pRequestDate        ,14 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pStoreId            ,10 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pOrderNumber        ,50 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pUserName           ,50 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pIdNum              ,13 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pEmail              ,50 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pGoodType           ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pGoodName           ,50 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pKeyInType          ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pLineType           ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pPhoneNo            ,12 ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pApprovalCount      ,1  ,'X'    );
		$TmpHeadMsg .= $this->fmt($_pFiller             ,35 ,'X'    );

		$this->SendHeadMsg  = $TmpHeadMsg;
	}

	// 카드Bin Check
	function CardBinViewDataMessage(
		$_pApprovalType      ,// 승인구분
		$_pTrackII           ,// 카드번호=유효기간
		$_pFiller            )// 예비
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType       ,    4, 'X');
		$TmpDataMsg .= $this->fmt($_pTrackII            ,   40, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller             ,   56, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	//신용카드승인요청 Body 1
	function CreditDataMessage(
		$_pApprovalType          ,// 승인구분
		$_pInterestType          ,// 일반/무이자구분 1:일반 2:무이자
		$_pTrackII               ,// 카드번호=유효기간  or 거래번호
		$_pInstallment           ,// 할부  00일시불
		$_pAmount                ,// 금액
		$_pPasswd                ,// 비밀번호 앞2자리
		$_pIdNum                 ,// 주민번호  뒤7자리, 사업자번호10
		$_pCurrencyType          ,// 통화구분 0:원화 1: 미화
		$_pBatchUseType          ,// 거래번호배치사용구분  0:미사용 1:사용
		$_pCardSendType          ,// 카드정보전송 0:미전송 2:카드번호앞14자리 + "XXXX",유효기간,할부,금액,가맹점번호
		$_pVisaAuthYn            ,// 비자인증유무 0:사용안함,7:SSL,9:비자인증
		$_pDomain                ,// 도메인 자체가맹점(PG업체용)
		$_pIpAddr                ,// IP ADDRESS 자체가맹점(PG업체용)
		$_pBusinessNumber        ,// 사업자 번호 자체가맹점(PG업체용)
		$_pFiller                ,// 예비
		$_pAuthType              ,// ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
		$_pMPIPositionType       ,// K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
		$_pMPIReUseType          ,// Y :  재사용, N : 재사용아님
		$_pEncData               )// MPI, ISP 데이터
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType       ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pInterestType       ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pTrackII            ,    40, 'X');
		$TmpDataMsg .= $this->fmt($_pInstallment        ,     2, '9');
		$TmpDataMsg .= $this->fmt($_pAmount             ,     9, '9');
		$TmpDataMsg .= $this->fmt($_pPasswd             ,     2, 'X');
		$TmpDataMsg .= $this->fmt($_pIdNum              ,    10, 'X');
		$TmpDataMsg .= $this->fmt($_pCurrencyType       ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pBatchUseType       ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pCardSendType       ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pVisaAuthYn         ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pDomain             ,    40, 'X');
		$TmpDataMsg .= $this->fmt($_pIpAddr             ,    20, 'X');
		$TmpDataMsg .= $this->fmt($_pBusinessNumber     ,    10, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller             ,   135, 'X');
		$TmpDataMsg .= $this->fmt($_pAuthType           ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pMPIPositionType    ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pMPIReUseType       ,     1, 'X');
		$TmpDataMsg .= $_pEncData                         ;

		$this->SendDataMsg .= $TmpDataMsg;
	}

	//가상계좌(pVersion:0603)
	function VirtualAccountDataMessage(
		$_pApprovalType      ,// 승인구분
		$_pBankCode          ,// 은행코드
		$_pAmount            ,// 금액
		$_pCloseDate         ,// 은행일
		$_pCloseTime         ,// 은행시간
		$_pEscrowSele        ,// 에스크로적용구분: 0:적용안함, 1:적용, 2:강제적용
		$_pVirFixSele        ,// 가상계좌번호지정구분
		$_pVirAcctNo         ,// 가상계좌번호
		$_pOrgTransactionNo  ,// 원거래거래번호
		$_pFiller            )// 예비
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType           ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pBankCode               ,     6, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount                 ,     9, '9');
		$TmpDataMsg .= $this->fmt($_pCloseDate              ,     8, 'X');
		$TmpDataMsg .= $this->fmt($_pCloseTime              ,     6, 'X');
		$TmpDataMsg .= $this->fmt($_pEscrowSele             ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pVirFixSele             ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pVirAcctNo              ,    15, 'X');
		$TmpDataMsg .= $this->fmt($_pOrgTransactionNo       ,    12, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller                 ,    52, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}
	// 계좌이체 시작 요청전문 생성(send)
	function AcctRequest_send(
		$_pApprovalType      ,// 승인구분
		$_pAcctSele          ,// 계좌이체 유형구문
		$_pFeeSele           ,// 선/후불제구분
		$_pPareBankCode      ,// 모계좌은행코드
		$_pPareAcctNo        ,// 모계좌번호
		$_pCustBankCode      ,// 고객계좌은행코드
		$_pAmount            ,// 금액
		$_pInjaName          ,// 인자명(상점명)
		$_pFiller              )// 기타
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType           ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pAcctSele               ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pFeeSele                ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pPareBankCode           ,     6, 'X');
		$TmpDataMsg .= $this->fmt($_pPareAcctNo             ,    15, 'X');
		$TmpDataMsg .= $this->fmt($_pCustBankCode           ,     6, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount                 ,    13, '9');
		$TmpDataMsg .= $this->fmt($_pInjaName               ,    16, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller                 ,    38, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	//계좌이체 인증승인 요청전문을 만든다.
	function AcctRequest_iappr(
		$_pApprovalType          ,// 승인구분    코드
		$_pAcctSele              ,// 계좌이체 구분 - 2:PopBanking,4:새마을금고,5:금결원,6:PopBanking(휴대폰인증),8:CMA계좌이체
		$_pFeeSele               ,// 계좌이체 구분 - 선/후불제구분 - 2:후불
		$_pTransactionNo         ,// 거래번호
		$_pBankCode              ,// 입금모계좌코드
		$_pAmount                ,// 금액    (결제대상금액)
		$_pCustBankInja          ,// 출금모계좌코드
		$_pBankTransactionNo     ,// 은행거래번호
		$_pFiller                ,//
		$_pCertData              )// 인증정보
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType               ,       4, 'X');
		$TmpDataMsg .= $this->fmt($_pAcctSele                   ,       1, 'X');
		$TmpDataMsg .= $this->fmt($_pFeeSele                    ,       1, 'X');
		$TmpDataMsg .= $this->fmt($_pTransactionNo              ,      12, 'X');
		$TmpDataMsg .= $this->fmt($_pBankCode                   ,       6, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount                     ,      13, '9');
		$TmpDataMsg .= $this->fmt($_pCustBankInja               ,      30, 'X');
		$TmpDataMsg .= $this->fmt($_pBankTransactionNo          ,      30, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller                     ,      53, 'X');
		$TmpDataMsg .= $_pCertData                                ;

		$this->SendDataMsg .= $TmpDataMsg;
	}

	// 월드패스승인
	function WorldPassDataMessage(
		$_pApprovalType          ,// 승인구분
		$_pTrackII               ,// 카드번호=4912  or 거래번호
		$_pPasswd                ,// 비밀번호 앞2자리
		$_pAmount                ,// 금액
		$_pWorldPassType         ,// 선후불카드구분
		$_pAdultType             ,// 성인확인구분
		$_pCardSendType          ,// 카드정보전송 0:미전송  2:카드번호앞14자리 + "XXXX",유효기간,할부,금액,가맹점번호
		$_pFiller                )// 예비
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType           ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pTrackII                ,    40, 'X');
		$TmpDataMsg .= $this->fmt($_pPasswd                 ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount                 ,     9, '9');
		$TmpDataMsg .= $this->fmt($_pWorldPassType          ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pAdultType              ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pCardSendType           ,     1, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller                 ,    40, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	// 포인트카드승인
	function PointDataMessage(
		$_pApprovalType      ,// 승인구분
		$_pTrackII           ,// 카드번호=유효기간  or 거래번호
		$_pAmount            ,// 금액
		$_pPasswd            ,// 비밀번호 앞4자리
		$_pSaleType          ,// 판매구분
		$_pFiller            )// 예비
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType           ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pTrackII                ,    40, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount                 ,     9, '9');
		$TmpDataMsg .= $this->fmt($_pPasswd                 ,     4, 'X');
		$TmpDataMsg .= $this->fmt($_pSaleType               ,     2, 'X');
		$TmpDataMsg .= $this->fmt($_pFiller                 ,    41, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	// 현금영수증승인
	function CashBillDataMessage(
		$_pApprovalType          ,// H000:일반발급, H200:계좌이체,   H600:가상계좌
		$_pTransactionNo         ,// 입금완료된 계좌이체, 가상계좌   거래번호
		$_pIssuSele              ,// 0:일반발급(PG원거래번호 중복체크), 1:단독발급(주문번호 중복체크 :   PG원거래 없음), 2:강제발급(중복체크 안함)
		$_pUserInfoSele          ,// 0:주민등록번호
		$_pUserInfo              ,// 주민등록번호
		$_pTranSele              ,// 0: 개인, 1: 사업자
		$_pCallCode              ,// 통화코드    (0: 원화, 1: 미화)
		$_pSupplyAmt             ,// 공급가액
		$_pTaxAmt                ,// 세금
		$_pSvcAmt                ,// 봉사료
		$_pTotAmt                ,// 현금영수증 발급금액
		$_pFiller                )// 예비
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType       ,4      ,'X');
		$TmpDataMsg .= $this->fmt($_pTransactionNo      ,12     ,'X');
		$TmpDataMsg .= $this->fmt($_pIssuSele           ,1      ,'X');
		$TmpDataMsg .= $this->fmt($_pUserInfoSele       ,1      ,'X');
		$TmpDataMsg .= $this->fmt($_pUserInfo           ,37     ,'X');
		$TmpDataMsg .= $this->fmt($_pTranSele           ,1      ,'X');
		$TmpDataMsg .= $this->fmt($_pCallCode           ,1      ,'X');
		$TmpDataMsg .= $this->fmt($_pSupplyAmt          ,9      ,'9');
		$TmpDataMsg .= $this->fmt($_pTaxAmt             ,9      ,'9');
		$TmpDataMsg .= $this->fmt($_pSvcAmt             ,9      ,'9');
		$TmpDataMsg .= $this->fmt($_pTotAmt             ,9      ,'9');
		$TmpDataMsg .= $this->fmt($_pFiller             ,147    ,'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	// 상품권 온라인 PIn발급 요청전문 생성
	function SticketPinDataMessage(
		 $_pApprovalType         ,// 승인구분 코드
		 $_pGoveCode             ,// 기관코드
		 $_pGoveSele             ,// 기관구분
		 $_pPinSele              ,// 문화 M / 게임문화 G
		 $_pAmount               ,// 결제금액
		 $_pFiller               )//
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType       ,   4, 'X');
		$TmpDataMsg .= $this->fmt($_pGoveCode           ,  10, 'X');
		$TmpDataMsg .= $this->fmt($_pGoveSele           ,  10, 'X');
		$TmpDataMsg .= $this->fmt($_pPinSele            ,   1, 'X');
		$TmpDataMsg .= $this->fmt($_pAmount             ,   9, '9');
		$TmpDataMsg .= $this->fmt($_pFiller             , 116, 'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	//취소
	function CancelDataMessage(
		$_pApprovalType      ,// 승인구분
		$_pCancelType        ,// 취소처리구분 0:거래번호, 1:주문번호
		$_pTransactionNo     ,// 거래번호
		$_pTradeDate         ,// 거래일자
		$_pOrderNumber       ,// 주문번호
		$_pFiller            )// 기타
	{
		$TmpDataMsg  = "";

		$TmpDataMsg .= $this->fmt($_pApprovalType           ,4  ,'X');
		$TmpDataMsg .= $this->fmt($_pCancelType             ,1  ,'X');
		$TmpDataMsg .= $this->fmt($_pTransactionNo          ,12 ,'X');
		$TmpDataMsg .= $this->fmt($_pTradeDate              ,8  ,'X');
		$TmpDataMsg .= $this->fmt($_pOrderNumber            ,50 ,'X');
		$TmpDataMsg .= $this->fmt($_pFiller                 ,75 ,'X');

		$this->SendDataMsg .= $TmpDataMsg;
	}

	function SendEncSocket()
	{
		$d3_buf = substr($this->SendHeadMsg,4) . $this->SendDataMsg;
		$kesock     = new KSPayEncSocket();
		$kesock->ks_seed_set_key(strftime("%Y%m%d%H:%M:%S"));
		
TraceLog ("kspay_send_socket(".$this->IPAddr.",".$this->Port.") send=[".$d3_buf."]");		
		$p_rbuf     = $kesock->kspay_send_socket($this->IPAddr, $this->Port, $d3_buf);
TraceLog ("kspay_send_socket(".$this->IPAddr.",".$this->Port.") recv=[".$p_rbuf."]");

		$kesock->close();

		return $this->ParseMessage($p_rbuf ,strlen($p_rbuf) ,false);
	}

	function SendHostSocket($encdata)
	{
		$mlen = 0;
		$midx = 0;

		$mlen =  4;                                               $midx += $mlen;
		$mlen =  1;                                               $midx += $mlen;
		$mlen =  4;$authcd     = substr($encdata, $midx,   $mlen);$midx += $mlen;
		$mlen = 16;$seed_key   = substr($encdata, $midx,   $mlen);$midx += $mlen;

		$e1_msg     = substr($encdata,$midx);

		$e2_msg     = urldecode($e1_msg);

		$e3_buf     = base64_decode($e2_msg);

		$kesock     = new KSPayEncSocket();

		$kesock->ks_seed_set_key($seed_key);

		$d3_buf     = $kesock->ks_seed_decrypt($e3_buf);

TraceLog ("kspay_send_socket(".$this->IPAddr.",".$this->Port.") send=[".$d3_buf."]");

		$p_rbuf     = $kesock->kspay_send_socket($this->IPAddr, $this->Port, $d3_buf);

TraceLog ("kspay_send_socket(".$this->IPAddr.",".$this->Port.") recv=[".$p_rbuf."]");

		$kesock->close();

		return $this->ParseMessage($p_rbuf ,strlen($p_rbuf) ,false);
	}

	//function SendCancel()
	//{
	//  $_pStatus       = "";
	//  $_pTransactionNo    = "";
	//
	//  if (strlen(ApprovalType) != 4 || strlen(StoreId) != 10) return false;
	//
	//  $apprType = substr(ApprovalType,0,3);
	//
	//  if ($apprType == "100" || $apprType == "130" || $apprType == "I00")
	//  {
	//      $_pStatus           = $Status;
	//      $_pTransactionNo    = $TransactionNo;
	//  }else
	//  if ($apprType == "242")
	//  {
	//      $_pStatus           = $ACStatus;
	//      $_pTransactionNo    = $ACTransactionNo;
	//  }else
	//  if ($apprType == "400" || $apprType == "410")
	//  {
	//      $_pStatus           = $PStatus;
	//      $_pTransactionNo    = $PTransactionNo;
	//  }else
	//  if ($apprType == "600")
	//  {
	//      $_pStatus           = $VAStatus;
	//      $_pTransactionNo    = $VATransactionNo;
	//  }else
	//  if ($apprType == "700")
	//  {
	//      $_pStatus           = $WPStatus;
	//      $_pTransactionNo    = $WPTransactionNo;
	//  }else
	//  if ($apprType == "H00" || $apprType == "H20" || $apprType == "H60")
	//  {
	//      $_pStatus           = $HStatus;
	//      $_pTransactionNo    = $HTransactionNo;
	//  }else
	//  {
	//      ;
	//  }
	//
	//  if (strlen(pStatus) != 1 || strlen(pTransactionNo) != 12 || pStatus.Equals("X")) return false;
	//
	//  SendHeadMsg     = "";
	//  SendDataMsg     = "";
	//
	//  ReceiveHeadMsg  = "";
	//  ReceiveDataMsg  = "";
	//
	//  SendCount       = 0;
	//  ReceiveCount    = 0;
	//
	//  HeadMessage("2", "0603", "00", "0", "", StoreId, "", "", "", "", "0", "", "K", "1", "", "1", "");
	//  CancelDataMessage(substr(ApprovalType,0,1) . "010", "0", _pTransactionNo, "", "", "" );
	//
	//  return SendEncSocket();
	//}

	function ParseMessage($read_bytes, $len, $useLen)
	{
		$idx = 0;
		if ($len < 300+50)
		{
TraceLog("WARN  : too short head_msg(" . $len . ")=[" . substr($read_bytes,0,$len) . "]!!");
			return false;
		}

		$TmpHeadMsg = "";

		if ($useLen)
		{
			$MsgLen = substr($read_bytes,$idx, 4); $idx +=    4; $TmpHeadMsg .= $MsgLen       ;// 데이터 길이
		}

		$this->EncType        = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->EncType      ;// 0: 암화안함, 1:openssl, 2: seed
		$this->Version        = substr($read_bytes,$idx, 4); $idx +=    4; $TmpHeadMsg .= $this->Version      ;// 전문버전
		$this->Type           = substr($read_bytes,$idx, 2); $idx +=    2; $TmpHeadMsg .= $this->Type         ;// 구분
		$this->Resend         = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->Resend       ;// 전송구분 : 0 : 처음,  2: 재전송
		$this->RequestDate    = substr($read_bytes,$idx,14); $idx +=   14; $TmpHeadMsg .= $this->RequestDate  ;// 요청일자 : yyyymmddhhmmss
		$this->StoreId        = substr($read_bytes,$idx,10); $idx +=   10; $TmpHeadMsg .= $this->StoreId      ;// 상점아이디
		$this->OrderNumber    = substr($read_bytes,$idx,50); $idx +=   50; $TmpHeadMsg .= $this->OrderNumber  ;// 주문번호
		$this->UserName       = substr($read_bytes,$idx,50); $idx +=   50; $TmpHeadMsg .= $this->UserName     ;// 주문자명
		$this->IdNum          = substr($read_bytes,$idx,13); $idx +=   13; $TmpHeadMsg .= $this->IdNum        ;// 주민번호 or 사업자번호
		$this->Email          = substr($read_bytes,$idx,50); $idx +=   50; $TmpHeadMsg .= $this->Email        ;// email
		$this->GoodType       = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->GoodType     ;// 제품구분 0 : 실물, 1 : 디지털
		$this->GoodName       = substr($read_bytes,$idx,50); $idx +=   50; $TmpHeadMsg .= $this->GoodName     ;// 제품명
		$this->KeyInType      = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->KeyInType    ;// KeyInType 여부 : 1 : Swap, 2: KeyIn
		$this->LineType       = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->LineType     ;// lineType 0 : offline, 1:internet, 2:Mobile
		$this->PhoneNo        = substr($read_bytes,$idx,12); $idx +=   12; $TmpHeadMsg .= $this->PhoneNo      ;// 휴대폰번호
		$this->ApprovalCount  = substr($read_bytes,$idx, 1); $idx +=    1; $TmpHeadMsg .= $this->ApprovalCount;// 승인갯수
		$this->HeadFiller     = substr($read_bytes,$idx,35); $idx +=   35; $TmpHeadMsg .= $this->HeadFiller   ;// 예비

		$this->ReceiveHeadMsg = $TmpHeadMsg;

		$TmpReceiveMsg = "";

		$this->ApprovalType   = substr($read_bytes,$idx, 4); $idx +=    4; $TmpReceiveMsg .= $this->ApprovalType ; // 승인구분

		if (substr($this->ApprovalType,0,3) == "150")
		{
			$this->TransactionNo      = substr($read_bytes,$idx,  12); $idx += 12;$TmpReceiveMsg .= $this->TransactionNo      ; // 거래번호
			$this->Status             = substr($read_bytes,$idx,   1); $idx +=  1;$TmpReceiveMsg .= $this->Status             ; // 상태 O : 승인, X : 거절
			$this->TradeDate          = substr($read_bytes,$idx,   8); $idx +=  8;$TmpReceiveMsg .= $this->TradeDate          ; // 거래일자
			$this->TradeTime          = substr($read_bytes,$idx,   6); $idx +=  6;$TmpReceiveMsg .= $this->TradeTime          ; // 거래시간
			$this->IssCode            = substr($read_bytes,$idx,   6); $idx +=  6;$TmpReceiveMsg .= $this->IssCode            ; // 발급사코드
			$this->Message1           = substr($read_bytes,$idx,  16); $idx += 16;$TmpReceiveMsg .= $this->Message1           ; // 메시지1
			$this->Message2           = substr($read_bytes,$idx,  16); $idx += 16;$TmpReceiveMsg .= $this->Message2           ; // 메시지2
			$this->Filler             = substr($read_bytes,$idx,  31); $idx += 31;$TmpReceiveMsg .= $this->Message2           ; // 예비
		}else
		if (substr($this->ApprovalType,0,2) == "16")
		{
TraceLog("WARN  : not_implemented_msg" . $ApprovalType . ")!!");
			return false;
		}else
		if (substr($this->ApprovalType,0,1) == "1" || substr($this->ApprovalType,0,1) == "I")
		{
			$this->TransactionNo      = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->TransactionNo      ; //거래번호
			$this->Status             = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->Status             ; //상태 O : 승인, X : 거절
			$this->TradeDate          = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->TradeDate          ; //거래일자
			$this->TradeTime          = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->TradeTime          ; //거래시간
			$this->IssCode            = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->IssCode            ; //발급사코드
			$this->AquCode            = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->AquCode            ; //매입사코드
			$this->AuthNo             = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->AuthNo             ; //승인번호 or 거절시 오류코드
			$this->Message1           = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->Message1           ; //메시지1
			$this->Message2           = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->Message2           ; //메시지2
			$this->CardNo             = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->CardNo             ; //카드번호
			$this->ExpDate            = substr($read_bytes,$idx,  4   ); $idx +=  4;       $TmpReceiveMsg .= $this->ExpDate            ; //유효기간
			$this->Installment        = substr($read_bytes,$idx,  2   ); $idx +=  2;       $TmpReceiveMsg .= $this->Installment        ; //할부
			$this->Amount             = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->Amount             ; //금액
			$this->MerchantNo         = substr($read_bytes,$idx, 15   ); $idx += 15;       $TmpReceiveMsg .= $this->MerchantNo         ; //가맹점번호
			$this->AuthSendType       = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->AuthSendType       ; //전송구분= new String($read(2));
			$this->ApprovalSendType   = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->ApprovalSendType   ; //전송구분(0 : 거절, 1 : 승인, 2: 원카드)
			$this->Point1             = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->Point1             ; //Point1
			$this->Point2             = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->Point2             ; //Point2
			$this->Point3             = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->Point3             ; //Point3
			$this->Point4             = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->Point4             ; //Point4
			$this->VanTransactionNo   = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->VanTransactionNo   ; //Point4
			$this->Filler             = substr($read_bytes,$idx, 82   ); $idx += 82;       $TmpReceiveMsg .= $this->Filler             ; //예비
			$this->AuthType           = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->AuthType           ; //I : ISP거래, M : MPI거래, SPACE : 일반거래

			if (strlen($read_bytes) - $idx > 7)
			{
				$this->MPIPositionType= substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->MPIPositionType    ; // K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
				$this->MPIReUseType   = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->MPIReUseType       ; // Y : 재사용, N : 재사용아님
				$this->EncData        = substr($read_bytes,$idx       );                   $TmpReceiveMsg .= $this->EncData            ; //
			}
		}else
		if (substr($this->ApprovalType,0,3) == "210" || substr($this->ApprovalType,0,3) == "240")
		{
			$this->ACTransactionNo    = substr($read_bytes,$idx, 12   ); $idx += 12;$TmpReceiveMsg .= $this->ACTransactionNo   ;  // 거래번호
			$this->ACStatus           = substr($read_bytes,$idx,  1   ); $idx +=  1;$TmpReceiveMsg .= $this->ACStatus          ;  // 오류구분:- O:승인 X:거절
			$this->ACTradeDate        = substr($read_bytes,$idx,  8   ); $idx +=  8;$TmpReceiveMsg .= $this->ACTradeDate       ;  // 거래 개시 일자(YYYYMMDD)
			$this->ACTradeTime        = substr($read_bytes,$idx,  6   ); $idx +=  6;$TmpReceiveMsg .= $this->ACTradeTime       ;  // 거래 개시 시간(HHMMSS)
			$this->ACAcctSele         = substr($read_bytes,$idx,  1   ); $idx +=  1;$TmpReceiveMsg .= $this->ACAcctSele        ;  // 계좌이체 구분 -1:Dacom, 2:Pop Banking,  3:실시간계좌이체, 4:X
			$this->ACFeeSele          = substr($read_bytes,$idx,  1   ); $idx +=  1;$TmpReceiveMsg .= $this->ACFeeSele         ;  // 선/후불제구분 -1:선불,2:후불
			$this->ACPareBankCode     = substr($read_bytes,$idx,  6   ); $idx +=  6;$TmpReceiveMsg .= $this->ACPareBankCode    ;  // 입금모계좌은행코드
			$this->ACPareAcctNo       = substr($read_bytes,$idx, 15   ); $idx += 15;$TmpReceiveMsg .= $this->ACPareAcctNo      ;  // 입금모계좌 번호
			$this->ACCustBankCode     = substr($read_bytes,$idx,  6   ); $idx +=  6;$TmpReceiveMsg .= $this->ACCustBankCode    ;  // 출급은행코드
			$this->ACAmount           = substr($read_bytes,$idx, 13   ); $idx += 13;$TmpReceiveMsg .= $this->ACAmount          ;  // 금액
			$this->ACInjaName         = substr($read_bytes,$idx, 16   ); $idx += 16;$TmpReceiveMsg .= $this->ACInjaName        ;  // 인자명(상점명)
			$this->ACMessage1         = substr($read_bytes,$idx, 16   ); $idx += 16;$TmpReceiveMsg .= $this->ACMessage1        ;  // 응답 message1
			$this->ACMessage2         = substr($read_bytes,$idx, 16   ); $idx += 16;$TmpReceiveMsg .= $this->ACMessage2        ;  // 응답 message2
			$this->ACEntrNumb         = substr($read_bytes,$idx, 10   ); $idx += 10;$TmpReceiveMsg .= $this->ACEntrNumb        ;  // 사업자번호
			$this->ACShopPhone        = substr($read_bytes,$idx, 20   ); $idx += 20;$TmpReceiveMsg .= $this->ACShopPhone       ;  // 전화번호
			$this->ACFiller           = substr($read_bytes,$idx, 49   ); $idx += 49;$TmpReceiveMsg .= $this->ACFiller          ;  // 예비
TraceLog("WARN    : not_implemented_msg" . $ApprovalType . ")!!");

		}else
		if (substr($this->ApprovalType,0,1) == "2")
		{
			$this->ACTransactionNo     = substr($read_bytes,$idx, 12); $idx += 12;$TmpReceiveMsg .= $this->ACTransactionNo      ; // 거래번호
			$this->ACStatus            = substr($read_bytes,$idx,  1); $idx +=  1;$TmpReceiveMsg .= $this->ACStatus             ; // 오류구분 :승인 X:거절
			$this->ACTradeDate         = substr($read_bytes,$idx,  8); $idx +=  8;$TmpReceiveMsg .= $this->ACTradeDate          ; // 거래 개시 일자(YYYYMMDD)
			$this->ACTradeTime         = substr($read_bytes,$idx,  6); $idx +=  6;$TmpReceiveMsg .= $this->ACTradeTime          ; // 거래 개시 시간(HHMMSS)
			$this->ACAcctSele          = substr($read_bytes,$idx,  1); $idx +=  1;$TmpReceiveMsg .= $this->ACAcctSele           ; // 계좌이체 구분 -   1:Dacom, 2:Pop Banking, 3:실시간계좌이체 4: 승인형계좌이체
			$this->ACFeeSele           = substr($read_bytes,$idx,  1); $idx +=  1;$TmpReceiveMsg .= $this->ACFeeSele            ; // 선/후불제구분 -   1:선불, 2:후불
			$this->ACInjaName          = substr($read_bytes,$idx, 16); $idx += 16;$TmpReceiveMsg .= $this->ACInjaName           ; // 인자명(통장인쇄메세지-상점명)
			$this->ACPareBankCode      = substr($read_bytes,$idx,  6); $idx +=  6;$TmpReceiveMsg .= $this->ACPareBankCode       ; // 입금모계좌코드
			$this->ACPareAcctNo        = substr($read_bytes,$idx, 15); $idx += 15;$TmpReceiveMsg .= $this->ACPareAcctNo         ; // 입금모계좌번호
			$this->ACCustBankCode      = substr($read_bytes,$idx,  6); $idx +=  6;$TmpReceiveMsg .= $this->ACCustBankCode       ; // 출금모계좌코드
			$this->ACCustAcctNo        = substr($read_bytes,$idx, 15); $idx += 15;$TmpReceiveMsg .= $this->ACCustAcctNo         ; // 출금모계좌번호
			$this->ACAmount            = substr($read_bytes,$idx, 13); $idx += 13;$TmpReceiveMsg .= $this->ACAmount             ; // 금액  (결제대상금액)
			$this->ACBankTransactionNo = substr($read_bytes,$idx, 30); $idx += 30;$TmpReceiveMsg .= $this->ACBankTransactionNo  ; // 은행거래번호
			$this->ACIpgumNm           = substr($read_bytes,$idx, 20); $idx += 20;$TmpReceiveMsg .= $this->ACIpgumNm            ; // 입금자명
			$this->ACBankFee           = substr($read_bytes,$idx, 13); $idx += 13;$TmpReceiveMsg .= $this->ACBankFee            ; // 계좌이체 수수료
			$this->ACBankAmount        = substr($read_bytes,$idx, 13); $idx += 13;$TmpReceiveMsg .= $this->ACBankAmount         ; // 총결제금액(결제대상금액+ 수수료
			$this->ACBankRespCode      = substr($read_bytes,$idx,  4); $idx +=  4;$TmpReceiveMsg .= $this->ACBankRespCode       ; // 오류코드
			$this->ACMessage1          = substr($read_bytes,$idx, 16); $idx += 16;$TmpReceiveMsg .= $this->ACMessage1           ; // 오류 message 1
			$this->ACMessage2          = substr($read_bytes,$idx, 16); $idx += 16;$TmpReceiveMsg .= $this->ACMessage2           ; // 오류 message 2
			$this->ACCavvSele          = substr($read_bytes,$idx,  1); $idx +=  1;$TmpReceiveMsg .= $this->ACCavvSele           ; // 암호화데이터응답여부
			$this->ACFiller            = substr($read_bytes,$idx,183); $idx +=183;$TmpReceiveMsg .= $this->ACFiller             ; // 예비

			$EncLen = "";
			$this->ACEncData = "";
			if( $this->ACCavvSele == "1" )
			{
				$EncLen               = substr($read_bytes,$idx, 5); $idx +=   5      ;$TmpReceiveMsg .= $EncLen            ;
				$this->ACEncData      = substr($read_bytes,$idx, $EncLen)             ;$TmpReceiveMsg .= $this->ACEncData         ; // 금결원암호화응답
			}               ;
		}else
		if (substr($this->ApprovalType,0,1) == "4")
		{
			$this->PTransactionNo     = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->PTransactionNo     ; // 거래번호
			$this->PStatus            = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->PStatus            ; // 상태 O : 승인 , X : 거절
			$this->PTradeDate         = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->PTradeDate         ; // 거래일자
			$this->PTradeTime         = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->PTradeTime         ; // 거래시간
			$this->PIssCode           = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->PIssCode           ; // 발급사코드
			$this->PAuthNo            = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->PAuthNo            ; // 승인번호 or 거절시 오류코드
			$this->PMessage1          = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->PMessage1          ; // 메시지1
			$this->PMessage2          = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->PMessage2          ; // 메시지2
			$this->PPoint1            = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->PPoint1            ; // 거래포인트
			$this->PPoint2            = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->PPoint2            ; // 가용포인트
			$this->PPoint3            = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->PPoint3            ; // 누적포인트
			$this->PPoint4            = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->PPoint4            ; // 가맹점포인트
			$this->PMerchantNo        = substr($read_bytes,$idx, 15   ); $idx += 15;       $TmpReceiveMsg .= $this->PMerchantNo        ; // 가맹점번호
			$this->PNotice1           = substr($read_bytes,$idx, 40   ); $idx += 40;       $TmpReceiveMsg .= $this->PNotice1           ; //
			$this->PNotice2           = substr($read_bytes,$idx, 40   ); $idx += 40;       $TmpReceiveMsg .= $this->PNotice2           ; //
			$this->PNotice3           = substr($read_bytes,$idx, 40   ); $idx += 40;       $TmpReceiveMsg .= $this->PNotice3           ; //
			$this->PNotice4           = substr($read_bytes,$idx, 40   ); $idx += 40;       $TmpReceiveMsg .= $this->PNotice4           ; //
			$this->PFiller            = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->PFiller            ; // 예비
		}else
		if (substr($this->ApprovalType,0,2) == "60")
		{
			$this->VATransactionNo        = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->VATransactionNo            ; // 거래번호
			$this->VAStatus               = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->VAStatus                   ; // 상태
			$this->VATradeDate            = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->VATradeDate                ; // 거래일자
			$this->VATradeTime            = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->VATradeTime                ; // 거래시간
			$this->VABankCode             = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->VABankCode                 ; // 은행코드
			$this->VAVirAcctNo            = substr($read_bytes,$idx, 15   ); $idx += 15;       $TmpReceiveMsg .= $this->VAVirAcctNo                ; // 가상계좌번호
			$this->VAName                 = substr($read_bytes,$idx, 30   ); $idx += 30;       $TmpReceiveMsg .= $this->VAName                     ; // 예금주명
			$this->VACloseDate            = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->VACloseDate                ; // 은행확인일
			$this->VACloseTime            = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->VACloseTime                ; // 은행확인시간
			$this->VARespCode             = substr($read_bytes,$idx,  4   ); $idx +=  4;       $TmpReceiveMsg .= $this->VARespCode                 ; // 응답코드
			$this->VAMessage1             = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->VAMessage1                 ; // 메시지1
			$this->VAMessage2             = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->VAMessage2                 ; // 메시지2
			$this->VAFiller               = substr($read_bytes,$idx, 36   ); $idx += 36;       $TmpReceiveMsg .= $this->VAFiller                   ; // 예비
		}else
		if (substr($this->ApprovalType,0,1) == "7")
		{
			$this->WPTransactionNo        = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->WPTransactionNo        ; // 거래번호
			$this->WPStatus               = substr($read_bytes,$idx,  1   ); $idx +=  1;       $TmpReceiveMsg .= $this->WPStatus               ; // 상태
			$this->WPTradeDate            = substr($read_bytes,$idx,  8   ); $idx +=  8;       $TmpReceiveMsg .= $this->WPTradeDate            ; // 거래일자
			$this->WPTradeTime            = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->WPTradeTime            ; // 거래시간
			$this->WPIssCode              = substr($read_bytes,$idx,  6   ); $idx +=  6;       $TmpReceiveMsg .= $this->WPIssCode              ; // 발급사코드
			$this->WPAuthNo               = substr($read_bytes,$idx, 12   ); $idx += 12;       $TmpReceiveMsg .= $this->WPAuthNo               ; // 승인번호
			$this->WPBalanceAmount        = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->WPBalanceAmount        ; // 잔액
			$this->WPLimitAmount          = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->WPLimitAmount          ; // 한도액
			$this->WPMessage1             = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->WPMessage1             ; // 메시지1
			$this->WPMessage2             = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->WPMessage2             ; // 메시지2
			$this->WPCardNo               = substr($read_bytes,$idx, 16   ); $idx += 16;       $TmpReceiveMsg .= $this->WPCardNo               ; // 카드번호
			$this->WPAmount               = substr($read_bytes,$idx,  9   ); $idx +=  9;       $TmpReceiveMsg .= $this->WPAmount               ; // 금액
			$this->WPMerchantNo           = substr($read_bytes,$idx, 15   ); $idx += 15;       $TmpReceiveMsg .= $this->WPMerchantNo           ; // 가맹점번호
			$this->WPFiller               = substr($read_bytes,$idx, 11   ); $idx += 11;       $TmpReceiveMsg .= $this->WPFiller               ; // 예비
		}else
		if (substr($this->ApprovalType,0,1) == "H")
		{
			$this->HTransactionNo         = substr($read_bytes,$idx,  12); $idx +=  12;        $TmpReceiveMsg .= $this->HTransactionNo         ; // 거래번호
			$this->HStatus                = substr($read_bytes,$idx,   1); $idx +=   1;        $TmpReceiveMsg .= $this->HStatus                ; // 오류구분 O:정상 X:거절
			$this->HCashTransactionNo     = substr($read_bytes,$idx,  12); $idx +=  12;        $TmpReceiveMsg .= $this->HCashTransactionNo     ; // 현금영수증 거래번호
			$this->HIncomeType            = substr($read_bytes,$idx,   1); $idx +=   1;        $TmpReceiveMsg .= $this->HIncomeType            ; // 0: 소득      1: 비소득
			$this->HTradeDate             = substr($read_bytes,$idx,   8); $idx +=   8;        $TmpReceiveMsg .= $this->HTradeDate             ; // 거래 개시 일자
			$this->HTradeTime             = substr($read_bytes,$idx,   6); $idx +=   6;        $TmpReceiveMsg .= $this->HTradeTime             ; // 거래 개시 시간
			$this->HMessage1              = substr($read_bytes,$idx,  16); $idx +=  16;        $TmpReceiveMsg .= $this->HMessage1              ; // 응답 message1
			$this->HMessage2              = substr($read_bytes,$idx,  16); $idx +=  16;        $TmpReceiveMsg .= $this->HMessage2              ; // 응답 message2
			$this->HCashMessage1          = substr($read_bytes,$idx,  20); $idx +=  20;        $TmpReceiveMsg .= $this->HCashMessage1          ; // 국세청 메시지 1
			$this->HCashMessage2          = substr($read_bytes,$idx,  20); $idx +=  20;        $TmpReceiveMsg .= $this->HCashMessage2          ; // 국세청 메시지 2
			$this->HFiller                = substr($read_bytes,$idx, 150); $idx += 150;        $TmpReceiveMsg .= $this->HFiller                ; // 예비
		}else
		if (substr($this->ApprovalType,0,1) == "S")
		{
			$this->STTransactionNo        = substr($read_bytes,$idx,  12); $idx +=  12;        $TmpReceiveMsg .= $this->STTransactionNo        ; // 거래번호
			$this->STStatus               = substr($read_bytes,$idx,   1); $idx +=   1;        $TmpReceiveMsg .= $this->STStatus               ; // 오류구분 - O:성공  X:실패 S : 확인필요
			$this->STTradeDate            = substr($read_bytes,$idx,   8); $idx +=   8;        $TmpReceiveMsg .= $this->STTradeDate            ; // 거래일자
			$this->STTradeTime            = substr($read_bytes,$idx,   6); $idx +=   6;        $TmpReceiveMsg .= $this->STTradeTime            ; // 거래시간
			$this->STGoveSele             = substr($read_bytes,$idx,  10); $idx +=  10;        $TmpReceiveMsg .= $this->STGoveSele             ; // 기관구분
			$this->STPinType              = substr($read_bytes,$idx,   1); $idx +=   1;        $TmpReceiveMsg .= $this->STPinType              ; // 문화 M / 게임문화 G
			$this->STAuthNo               = substr($read_bytes,$idx,  20); $idx +=  20;        $TmpReceiveMsg .= $this->STAuthNo               ; // 승인번호 - 오류시 오류코드
			$this->STRespMsg              = substr($read_bytes,$idx,  50); $idx +=  50;        $TmpReceiveMsg .= $this->STRespMsg              ; // 메시지
			$this->STAmount               = substr($read_bytes,$idx,   9); $idx +=   9;        $TmpReceiveMsg .= $this->STAmount               ; // 결제금액
			$this->STPinNumb              = substr($read_bytes,$idx,  18); $idx +=  18;        $TmpReceiveMsg .= $this->STPinNumb              ; // PIN 번호
			$this->STCertNo               = substr($read_bytes,$idx,  16); $idx +=  16;        $TmpReceiveMsg .= $this->STCertNo               ; // 관리번호
			$this->STExpDate              = substr($read_bytes,$idx,   4); $idx +=   4;        $TmpReceiveMsg .= $this->STExpDate              ; // 유효기간
			$this->STFiller               = substr($read_bytes,$idx,  87); $idx +=  87;        $TmpReceiveMsg .= $this->STFiller               ; // 예비
		}else
		{
TraceLog("WARN  : undefined type_msg(" . $ApprovalType . ")=[" . substr(read_bytes,0,len) . "]!!");
			return false;
		}
		$this->ReceiveDataMsg = $TmpReceiveMsg;

		return true;
	}

}

class KSPayEncSocket
{	
	private $fp_socket;
	private $seed;
	private $seedKey;
	private $pdwRoundKey;

	function ks_rsa_encrypt($message)
	{
		$rsa = new RSA();

		$modulus            = "149234705969702295610824639493619264639191052081409168648733303710778928855302427002150156226358372843497166916761093271814505405085396163110795867804091173734918786604895614138245233907369511767692945734539378675868409865204298121111392354219378026082752930370002524206868296314546596840426179006142059188173";
		$publicExponent     = "65537";

		return $rsa->rsa_encrypt($message, $publicExponent, $modulus, 1024);
	}

	function ks_seed_set_key($p_kbuf)
	{
		$this->seed = new Seed();
		$this->seedKey = $p_kbuf;

		$key_arr = array();
		for($i=0;$i<strlen($p_kbuf);$i++ ) $key_arr[$i] = ord($p_kbuf{$i});

		$this->seed->SeedRoundKey($this->pdwRoundKey, $key_arr);
	}

	function ks_seed_encrypt($src)
	{
		return $this->seed->Encrypt($src, $this->pdwRoundKey, true);
	}

	function ks_seed_decrypt($src)
	{
		return $this->seed->Decrypt($src, $this->pdwRoundKey, true);
	}

	function kspay_send_socket($ipaddr, $port, $p_dbuf)
	{
		$e_kbuf	= $this->ks_rsa_encrypt($this->seedKey);

		$e_dbuf	= $this->ks_seed_encrypt($p_dbuf);
		
		$lbuf	= "0000" . strlen($e_dbuf);
		$lbuf	= substr($lbuf,strlen($lbuf)-4);
		$e_sbuf	= "01292" . $e_kbuf . $lbuf . $e_dbuf;

		$this->fp_socket = fsockopen($ipaddr, $port, $errno, $errstr, 60);
		if($this->fp_socket) {
			fwrite($this->fp_socket,$e_sbuf, strlen($e_sbuf));
			while(!feof($this->fp_socket)) {
				$e_rbuf .= fread($this->fp_socket, 8192);
			}
		}

		return $this->ks_seed_decrypt(substr($e_rbuf,4));
	}

	function close()
	{
		if ($this->fp_socket) fclose($this->fp_socket);
	}
}

class RSA
{
	// download from http://www.edsko.net/misc/rsa.php
	/*
	 * PHP implementation of the RSA algorithm
	 * (C) Copyright 2004 Edsko de Vries, Ireland
	 *
	 * Licensed under the GNU Public License (GPL)
	 *
	 * This implementation has been verified against [3]
	 * (tested Java/PHP interoperability).
	 *
	 * References:
	 * [1] "Applied Cryptography", Bruce Schneier, John Wiley & Sons, 1996
	 * [2] "Prime Number Hide-and-Seek", Brian Raiter, Muppetlabs (online)
	 * [3] "The Bouncy Castle Crypto Package", Legion of the Bouncy Castle,
	 *      (open source cryptography library for Java, online)
	 * [4] "PKCS #1: RSA Encryption Standard", RSA Laboratories Technical Note,
	 *      version 1.5, revised November 1, 1993
	 */

	/*
	 * Functions that are meant to be used by the user of this PHP module.
	 *
	 * Notes:
	 * - $key and $modulus should be numbers in (decimal) string format
	 * - $message is expected to be binary data
	 * - $keylength should be a multiple of 8, and should be in bits
	 * - For rsa_encrypt/rsa_sign, the length of $message should not exceed
	 *   ($keylength / 8) - 11 (as mandated by [4]).
	 * - rsa_encrypt and rsa_sign will automatically add padding to the message.
	 *   For rsa_encrypt, this padding will consist of random values; for rsa_sign,
	 *   padding will consist of the appropriate number of 0xFF values (see [4])
	 * - rsa_decrypt and rsa_verify will automatically remove message padding.
	 * - Blocks for decoding (rsa_decrypt, rsa_verify) should be exactly
	 *   ($keylength / 8) bytes long.
	 * - rsa_encrypt and rsa_verify expect a public key; rsa_decrypt and rsa_sign
	 *   expect a private key.
	 */

	function rsa_encrypt($message, $public_key, $modulus, $keylength)
	{
		$padded = $this->add_PKCS1_padding($message, true, $keylength / 8);
		$number = $this->binary_to_number($padded);
		$encrypted = $this->pow_mod($number, $public_key, $modulus);
		$result = $this->number_to_binary($encrypted, $keylength / 8);

		return $result;
	}

	function rsa_decrypt($message, $private_key, $modulus, $keylength)
	{
		$number = $this->binary_to_number($message);
		$decrypted = $this->pow_mod($number, $private_key, $modulus);
		$result = $this->number_to_binary($decrypted, $keylength / 8);

		return $this->remove_PKCS1_padding($result, $keylength / 8);
	}

	function rsa_sign($message, $private_key, $modulus, $keylength)
	{
		$padded = $this->add_PKCS1_padding($message, false, $keylength / 8);
		$number = $this->binary_to_number($padded);
		$signed = $this->pow_mod($number, $private_key, $modulus);
		$result = $this->number_to_binary($signed, $keylength / 8);

		return $result;
	}

	function rsa_verify($message, $public_key, $modulus, $keylength)
	{
		return $this->rsa_decrypt($message, $public_key, $modulus, $keylength);
	}

	function rsa_kyp_verify($message, $public_key, $modulus, $keylength)
	{
		$number = $this->binary_to_number($message);
		$decrypted = $this->pow_mod($number, $public_key, $modulus);
		$result = $this->number_to_binary($decrypted, $keylength / 8);

		return $this->remove_KYP_padding($result, $keylength / 8);
	}

	/*
	 * Some constants
	 */

	//define("BCCOMP_LARGER", 1);

	/*
	 * The actual implementation.
	 * Requires BCMath support in PHP (compile with --enable-bcmath)
	 */

	//--
	// Calculate (p ^ q) mod r
	//
	// We need some trickery to [2]:
	//   (a) Avoid calculating (p ^ q) before (p ^ q) mod r, because for typical RSA
	//       applications, (p ^ q) is going to be _WAY_ too large.
	//       (I mean, __WAY__ too large - won't fit in your computer's memory.)
	//   (b) Still be reasonably efficient.
	//
	// We assume p, q and r are all positive, and that r is non-zero.
	//
	// Note that the more simple algorithm of multiplying $p by itself $q times, and
	// applying "mod $r" at every step is also valid, but is O($q), whereas this
	// algorithm is O(log $q). Big difference.
	//
	// As far as I can see, the algorithm I use is optimal; there is no redundancy
	// in the calculation of the partial results.
	//--
	function pow_mod($p, $q, $r)
	{
		
		// Extract powers of 2 from $q
		$factors = array();
		$div = $q;
		$power_of_two = 0;
		//while(bccomp($div, "0") == BCCOMP_LARGER)
		while(bccomp($div, "0") == 1)
		{
			$rem = bcmod($div, 2);
			$div = bcdiv($div, 2);

			if($rem) array_push($factors, $power_of_two);
			$power_of_two++;
		}
		
		// Calculate partial results for each factor, using each partial result as a
		// starting point for the next. This depends of the factors of two being
		// generated in increasing order.
		$partial_results = array();
		$part_res = $p;
		$idx = 0;
		foreach($factors as $factor)
		{
			while($idx < $factor)
			{
				$part_res = bcpow($part_res, "2");
				$part_res = bcmod($part_res, $r);

				$idx++;
			}

			array_push($partial_results, $part_res);
		}
		
		// Calculate final result
		$result = "1";
		foreach($partial_results as $part_res)
		{
			$result = bcmul($result, $part_res);
			$result = bcmod($result, $r);
		}

		return $result;
	}

	//--
	// Function to add padding to a decrypted string
	// We need to know if this is a private or a public key operation [4]
	//--
	function add_PKCS1_padding($data, $isPublicKey, $blocksize)
	{
		$pad_length = $blocksize - 3 - strlen($data);

		if($isPublicKey)
		{
			$block_type = "\x02";

			$padding = "";
			for($i = 0; $i < $pad_length; $i++)
			{
				$rnd = mt_rand(1, 255);
				$padding .= chr($rnd);
			}
		}
		else
		{
			$block_type = "\x01";
			$padding = str_repeat("\xFF", $pad_length);
		}

		return "\x00" . $block_type . $padding . "\x00" . $data;
	}

	//--
	// Remove padding from a decrypted string
	// See [4] for more details.
	//--
	function remove_PKCS1_padding($data, $blocksize)
	{
		assert(strlen($data) == $blocksize);
		$data = substr($data, 1);

		// We cannot deal with block type 0
		if($data{0} == '\0')
			die("Block type 0 not implemented.");

		// Then the block type must be 1 or 2
		assert(($data{0} == "\x01") || ($data{0} == "\x02"));

		// Remove the padding
		$offset = strpos($data, "\0", 1);
		return substr($data, $offset + 1);
	}

	//--
	// Remove "kyp" padding
	// (Non standard)
	//--
	function remove_KYP_padding($data, $blocksize)
	{
		assert(strlen($data) == $blocksize);

		$offset = strpos($data, "\0");
		return substr($data, 0, $offset);
	}

	//--
	// Convert binary data to a decimal number
	//--
	function binary_to_number($data)
	{
		$base = "256";
		$radix = "1";
		$result = "0";

		for($i = strlen($data) - 1; $i >= 0; $i--)
		{
			$digit = ord($data{$i});
			$part_res = bcmul($digit, $radix);
			$result = bcadd($result, $part_res);
			$radix = bcmul($radix, $base);
		}

		return $result;
	}

	//--
	// Convert a number back into binary form
	//--
	function number_to_binary($number, $blocksize)
	{
		$base = "256";
		$result = "";

		$div = $number;
		while($div > 0)
		{
			$mod = bcmod($div, $base);
			$div = bcdiv($div, $base);

			$result = chr($mod) . $result;
		}

		return str_pad($result, $blocksize, "\x00", STR_PAD_LEFT);
	}
}

	/*******************************************************************************
	* Made      :   2011.01.21
	* Update        :   2011.01.21
	*
	* FILE   : class.seed.php
	* Convert PHP : mibany (2011-01-07) http://cena.co.kr/mibany
	*
	* DESCRIPTION: Core routines for the enhanced SEED
	*
	*******************************************************************************/

	/********************************* S-box table ********************************/

class Seed
{
	private $SS0 = array(
		0x2989a1a8, 0x05858184, 0x16c6d2d4, 0x13c3d3d0, 0x14445054, 0x1d0d111c, 0x2c8ca0ac, 0x25052124,
		0x1d4d515c, 0x03434340, 0x18081018, 0x1e0e121c, 0x11415150, 0x3cccf0fc, 0x0acac2c8, 0x23436360,
		0x28082028, 0x04444044, 0x20002020, 0x1d8d919c, 0x20c0e0e0, 0x22c2e2e0, 0x08c8c0c8, 0x17071314,
		0x2585a1a4, 0x0f8f838c, 0x03030300, 0x3b4b7378, 0x3b8bb3b8, 0x13031310, 0x12c2d2d0, 0x2ecee2ec,
		0x30407070, 0x0c8c808c, 0x3f0f333c, 0x2888a0a8, 0x32023230, 0x1dcdd1dc, 0x36c6f2f4, 0x34447074,
		0x2ccce0ec, 0x15859194, 0x0b0b0308, 0x17475354, 0x1c4c505c, 0x1b4b5358, 0x3d8db1bc, 0x01010100,
		0x24042024, 0x1c0c101c, 0x33437370, 0x18889098, 0x10001010, 0x0cccc0cc, 0x32c2f2f0, 0x19c9d1d8,
		0x2c0c202c, 0x27c7e3e4, 0x32427270, 0x03838380, 0x1b8b9398, 0x11c1d1d0, 0x06868284, 0x09c9c1c8,
		0x20406060, 0x10405050, 0x2383a3a0, 0x2bcbe3e8, 0x0d0d010c, 0x3686b2b4, 0x1e8e929c, 0x0f4f434c,
		0x3787b3b4, 0x1a4a5258, 0x06c6c2c4, 0x38487078, 0x2686a2a4, 0x12021210, 0x2f8fa3ac, 0x15c5d1d4,
		0x21416160, 0x03c3c3c0, 0x3484b0b4, 0x01414140, 0x12425250, 0x3d4d717c, 0x0d8d818c, 0x08080008,
		0x1f0f131c, 0x19899198, 0x00000000, 0x19091118, 0x04040004, 0x13435350, 0x37c7f3f4, 0x21c1e1e0,
		0x3dcdf1fc, 0x36467274, 0x2f0f232c, 0x27072324, 0x3080b0b0, 0x0b8b8388, 0x0e0e020c, 0x2b8ba3a8,
		0x2282a2a0, 0x2e4e626c, 0x13839390, 0x0d4d414c, 0x29496168, 0x3c4c707c, 0x09090108, 0x0a0a0208,
		0x3f8fb3bc, 0x2fcfe3ec, 0x33c3f3f0, 0x05c5c1c4, 0x07878384, 0x14041014, 0x3ecef2fc, 0x24446064,
		0x1eced2dc, 0x2e0e222c, 0x0b4b4348, 0x1a0a1218, 0x06060204, 0x21012120, 0x2b4b6368, 0x26466264,
		0x02020200, 0x35c5f1f4, 0x12829290, 0x0a8a8288, 0x0c0c000c, 0x3383b3b0, 0x3e4e727c, 0x10c0d0d0,
		0x3a4a7278, 0x07474344, 0x16869294, 0x25c5e1e4, 0x26062224, 0x00808080, 0x2d8da1ac, 0x1fcfd3dc,
		0x2181a1a0, 0x30003030, 0x37073334, 0x2e8ea2ac, 0x36063234, 0x15051114, 0x22022220, 0x38083038,
		0x34c4f0f4, 0x2787a3a4, 0x05454144, 0x0c4c404c, 0x01818180, 0x29c9e1e8, 0x04848084, 0x17879394,
		0x35053134, 0x0bcbc3c8, 0x0ecec2cc, 0x3c0c303c, 0x31417170, 0x11011110, 0x07c7c3c4, 0x09898188,
		0x35457174, 0x3bcbf3f8, 0x1acad2d8, 0x38c8f0f8, 0x14849094, 0x19495158, 0x02828280, 0x04c4c0c4,
		0x3fcff3fc, 0x09494148, 0x39093138, 0x27476364, 0x00c0c0c0, 0x0fcfc3cc, 0x17c7d3d4, 0x3888b0b8,
		0x0f0f030c, 0x0e8e828c, 0x02424240, 0x23032320, 0x11819190, 0x2c4c606c, 0x1bcbd3d8, 0x2484a0a4,
		0x34043034, 0x31c1f1f0, 0x08484048, 0x02c2c2c0, 0x2f4f636c, 0x3d0d313c, 0x2d0d212c, 0x00404040,
		0x3e8eb2bc, 0x3e0e323c, 0x3c8cb0bc, 0x01c1c1c0, 0x2a8aa2a8, 0x3a8ab2b8, 0x0e4e424c, 0x15455154,
		0x3b0b3338, 0x1cccd0dc, 0x28486068, 0x3f4f737c, 0x1c8c909c, 0x18c8d0d8, 0x0a4a4248, 0x16465254,
		0x37477374, 0x2080a0a0, 0x2dcde1ec, 0x06464244, 0x3585b1b4, 0x2b0b2328, 0x25456164, 0x3acaf2f8,
		0x23c3e3e0, 0x3989b1b8, 0x3181b1b0, 0x1f8f939c, 0x1e4e525c, 0x39c9f1f8, 0x26c6e2e4, 0x3282b2b0,
		0x31013130, 0x2acae2e8, 0x2d4d616c, 0x1f4f535c, 0x24c4e0e4, 0x30c0f0f0, 0x0dcdc1cc, 0x08888088,
		0x16061214, 0x3a0a3238, 0x18485058, 0x14c4d0d4, 0x22426260, 0x29092128, 0x07070304, 0x33033330,
		0x28c8e0e8, 0x1b0b1318, 0x05050104, 0x39497178, 0x10809090, 0x2a4a6268, 0x2a0a2228, 0x1a8a9298
	);

	private $SS1 = array(
		0x38380830, 0xe828c8e0, 0x2c2d0d21, 0xa42686a2, 0xcc0fcfc3, 0xdc1eced2, 0xb03383b3, 0xb83888b0,
		0xac2f8fa3, 0x60204060, 0x54154551, 0xc407c7c3, 0x44044440, 0x6c2f4f63, 0x682b4b63, 0x581b4b53,
		0xc003c3c3, 0x60224262, 0x30330333, 0xb43585b1, 0x28290921, 0xa02080a0, 0xe022c2e2, 0xa42787a3,
		0xd013c3d3, 0x90118191, 0x10110111, 0x04060602, 0x1c1c0c10, 0xbc3c8cb0, 0x34360632, 0x480b4b43,
		0xec2fcfe3, 0x88088880, 0x6c2c4c60, 0xa82888a0, 0x14170713, 0xc404c4c0, 0x14160612, 0xf434c4f0,
		0xc002c2c2, 0x44054541, 0xe021c1e1, 0xd416c6d2, 0x3c3f0f33, 0x3c3d0d31, 0x8c0e8e82, 0x98188890,
		0x28280820, 0x4c0e4e42, 0xf436c6f2, 0x3c3e0e32, 0xa42585a1, 0xf839c9f1, 0x0c0d0d01, 0xdc1fcfd3,
		0xd818c8d0, 0x282b0b23, 0x64264662, 0x783a4a72, 0x24270723, 0x2c2f0f23, 0xf031c1f1, 0x70324272,
		0x40024242, 0xd414c4d0, 0x40014141, 0xc000c0c0, 0x70334373, 0x64274763, 0xac2c8ca0, 0x880b8b83,
		0xf437c7f3, 0xac2d8da1, 0x80008080, 0x1c1f0f13, 0xc80acac2, 0x2c2c0c20, 0xa82a8aa2, 0x34340430,
		0xd012c2d2, 0x080b0b03, 0xec2ecee2, 0xe829c9e1, 0x5c1d4d51, 0x94148490, 0x18180810, 0xf838c8f0,
		0x54174753, 0xac2e8ea2, 0x08080800, 0xc405c5c1, 0x10130313, 0xcc0dcdc1, 0x84068682, 0xb83989b1,
		0xfc3fcff3, 0x7c3d4d71, 0xc001c1c1, 0x30310131, 0xf435c5f1, 0x880a8a82, 0x682a4a62, 0xb03181b1,
		0xd011c1d1, 0x20200020, 0xd417c7d3, 0x00020202, 0x20220222, 0x04040400, 0x68284860, 0x70314171,
		0x04070703, 0xd81bcbd3, 0x9c1d8d91, 0x98198991, 0x60214161, 0xbc3e8eb2, 0xe426c6e2, 0x58194951,
		0xdc1dcdd1, 0x50114151, 0x90108090, 0xdc1cccd0, 0x981a8a92, 0xa02383a3, 0xa82b8ba3, 0xd010c0d0,
		0x80018181, 0x0c0f0f03, 0x44074743, 0x181a0a12, 0xe023c3e3, 0xec2ccce0, 0x8c0d8d81, 0xbc3f8fb3,
		0x94168692, 0x783b4b73, 0x5c1c4c50, 0xa02282a2, 0xa02181a1, 0x60234363, 0x20230323, 0x4c0d4d41,
		0xc808c8c0, 0x9c1e8e92, 0x9c1c8c90, 0x383a0a32, 0x0c0c0c00, 0x2c2e0e22, 0xb83a8ab2, 0x6c2e4e62,
		0x9c1f8f93, 0x581a4a52, 0xf032c2f2, 0x90128292, 0xf033c3f3, 0x48094941, 0x78384870, 0xcc0cccc0,
		0x14150511, 0xf83bcbf3, 0x70304070, 0x74354571, 0x7c3f4f73, 0x34350531, 0x10100010, 0x00030303,
		0x64244460, 0x6c2d4d61, 0xc406c6c2, 0x74344470, 0xd415c5d1, 0xb43484b0, 0xe82acae2, 0x08090901,
		0x74364672, 0x18190911, 0xfc3ecef2, 0x40004040, 0x10120212, 0xe020c0e0, 0xbc3d8db1, 0x04050501,
		0xf83acaf2, 0x00010101, 0xf030c0f0, 0x282a0a22, 0x5c1e4e52, 0xa82989a1, 0x54164652, 0x40034343,
		0x84058581, 0x14140410, 0x88098981, 0x981b8b93, 0xb03080b0, 0xe425c5e1, 0x48084840, 0x78394971,
		0x94178793, 0xfc3cccf0, 0x1c1e0e12, 0x80028282, 0x20210121, 0x8c0c8c80, 0x181b0b13, 0x5c1f4f53,
		0x74374773, 0x54144450, 0xb03282b2, 0x1c1d0d11, 0x24250521, 0x4c0f4f43, 0x00000000, 0x44064642,
		0xec2dcde1, 0x58184850, 0x50124252, 0xe82bcbe3, 0x7c3e4e72, 0xd81acad2, 0xc809c9c1, 0xfc3dcdf1,
		0x30300030, 0x94158591, 0x64254561, 0x3c3c0c30, 0xb43686b2, 0xe424c4e0, 0xb83b8bb3, 0x7c3c4c70,
		0x0c0e0e02, 0x50104050, 0x38390931, 0x24260622, 0x30320232, 0x84048480, 0x68294961, 0x90138393,
		0x34370733, 0xe427c7e3, 0x24240420, 0xa42484a0, 0xc80bcbc3, 0x50134353, 0x080a0a02, 0x84078783,
		0xd819c9d1, 0x4c0c4c40, 0x80038383, 0x8c0f8f83, 0xcc0ecec2, 0x383b0b33, 0x480a4a42, 0xb43787b3
	);

	private $SS2 = array(
		0xa1a82989, 0x81840585, 0xd2d416c6, 0xd3d013c3, 0x50541444, 0x111c1d0d, 0xa0ac2c8c, 0x21242505,
		0x515c1d4d, 0x43400343, 0x10181808, 0x121c1e0e, 0x51501141, 0xf0fc3ccc, 0xc2c80aca, 0x63602343,
		0x20282808, 0x40440444, 0x20202000, 0x919c1d8d, 0xe0e020c0, 0xe2e022c2, 0xc0c808c8, 0x13141707,
		0xa1a42585, 0x838c0f8f, 0x03000303, 0x73783b4b, 0xb3b83b8b, 0x13101303, 0xd2d012c2, 0xe2ec2ece,
		0x70703040, 0x808c0c8c, 0x333c3f0f, 0xa0a82888, 0x32303202, 0xd1dc1dcd, 0xf2f436c6, 0x70743444,
		0xe0ec2ccc, 0x91941585, 0x03080b0b, 0x53541747, 0x505c1c4c, 0x53581b4b, 0xb1bc3d8d, 0x01000101,
		0x20242404, 0x101c1c0c, 0x73703343, 0x90981888, 0x10101000, 0xc0cc0ccc, 0xf2f032c2, 0xd1d819c9,
		0x202c2c0c, 0xe3e427c7, 0x72703242, 0x83800383, 0x93981b8b, 0xd1d011c1, 0x82840686, 0xc1c809c9,
		0x60602040, 0x50501040, 0xa3a02383, 0xe3e82bcb, 0x010c0d0d, 0xb2b43686, 0x929c1e8e, 0x434c0f4f,
		0xb3b43787, 0x52581a4a, 0xc2c406c6, 0x70783848, 0xa2a42686, 0x12101202, 0xa3ac2f8f, 0xd1d415c5,
		0x61602141, 0xc3c003c3, 0xb0b43484, 0x41400141, 0x52501242, 0x717c3d4d, 0x818c0d8d, 0x00080808,
		0x131c1f0f, 0x91981989, 0x00000000, 0x11181909, 0x00040404, 0x53501343, 0xf3f437c7, 0xe1e021c1,
		0xf1fc3dcd, 0x72743646, 0x232c2f0f, 0x23242707, 0xb0b03080, 0x83880b8b, 0x020c0e0e, 0xa3a82b8b,
		0xa2a02282, 0x626c2e4e, 0x93901383, 0x414c0d4d, 0x61682949, 0x707c3c4c, 0x01080909, 0x02080a0a,
		0xb3bc3f8f, 0xe3ec2fcf, 0xf3f033c3, 0xc1c405c5, 0x83840787, 0x10141404, 0xf2fc3ece, 0x60642444,
		0xd2dc1ece, 0x222c2e0e, 0x43480b4b, 0x12181a0a, 0x02040606, 0x21202101, 0x63682b4b, 0x62642646,
		0x02000202, 0xf1f435c5, 0x92901282, 0x82880a8a, 0x000c0c0c, 0xb3b03383, 0x727c3e4e, 0xd0d010c0,
		0x72783a4a, 0x43440747, 0x92941686, 0xe1e425c5, 0x22242606, 0x80800080, 0xa1ac2d8d, 0xd3dc1fcf,
		0xa1a02181, 0x30303000, 0x33343707, 0xa2ac2e8e, 0x32343606, 0x11141505, 0x22202202, 0x30383808,
		0xf0f434c4, 0xa3a42787, 0x41440545, 0x404c0c4c, 0x81800181, 0xe1e829c9, 0x80840484, 0x93941787,
		0x31343505, 0xc3c80bcb, 0xc2cc0ece, 0x303c3c0c, 0x71703141, 0x11101101, 0xc3c407c7, 0x81880989,
		0x71743545, 0xf3f83bcb, 0xd2d81aca, 0xf0f838c8, 0x90941484, 0x51581949, 0x82800282, 0xc0c404c4,
		0xf3fc3fcf, 0x41480949, 0x31383909, 0x63642747, 0xc0c000c0, 0xc3cc0fcf, 0xd3d417c7, 0xb0b83888,
		0x030c0f0f, 0x828c0e8e, 0x42400242, 0x23202303, 0x91901181, 0x606c2c4c, 0xd3d81bcb, 0xa0a42484,
		0x30343404, 0xf1f031c1, 0x40480848, 0xc2c002c2, 0x636c2f4f, 0x313c3d0d, 0x212c2d0d, 0x40400040,
		0xb2bc3e8e, 0x323c3e0e, 0xb0bc3c8c, 0xc1c001c1, 0xa2a82a8a, 0xb2b83a8a, 0x424c0e4e, 0x51541545,
		0x33383b0b, 0xd0dc1ccc, 0x60682848, 0x737c3f4f, 0x909c1c8c, 0xd0d818c8, 0x42480a4a, 0x52541646,
		0x73743747, 0xa0a02080, 0xe1ec2dcd, 0x42440646, 0xb1b43585, 0x23282b0b, 0x61642545, 0xf2f83aca,
		0xe3e023c3, 0xb1b83989, 0xb1b03181, 0x939c1f8f, 0x525c1e4e, 0xf1f839c9, 0xe2e426c6, 0xb2b03282,
		0x31303101, 0xe2e82aca, 0x616c2d4d, 0x535c1f4f, 0xe0e424c4, 0xf0f030c0, 0xc1cc0dcd, 0x80880888,
		0x12141606, 0x32383a0a, 0x50581848, 0xd0d414c4, 0x62602242, 0x21282909, 0x03040707, 0x33303303,
		0xe0e828c8, 0x13181b0b, 0x01040505, 0x71783949, 0x90901080, 0x62682a4a, 0x22282a0a, 0x92981a8a
	);

	private $SS3 = array(
		0x08303838, 0xc8e0e828, 0x0d212c2d, 0x86a2a426, 0xcfc3cc0f, 0xced2dc1e, 0x83b3b033, 0x88b0b838,
		0x8fa3ac2f, 0x40606020, 0x45515415, 0xc7c3c407, 0x44404404, 0x4f636c2f, 0x4b63682b, 0x4b53581b,
		0xc3c3c003, 0x42626022, 0x03333033, 0x85b1b435, 0x09212829, 0x80a0a020, 0xc2e2e022, 0x87a3a427,
		0xc3d3d013, 0x81919011, 0x01111011, 0x06020406, 0x0c101c1c, 0x8cb0bc3c, 0x06323436, 0x4b43480b,
		0xcfe3ec2f, 0x88808808, 0x4c606c2c, 0x88a0a828, 0x07131417, 0xc4c0c404, 0x06121416, 0xc4f0f434,
		0xc2c2c002, 0x45414405, 0xc1e1e021, 0xc6d2d416, 0x0f333c3f, 0x0d313c3d, 0x8e828c0e, 0x88909818,
		0x08202828, 0x4e424c0e, 0xc6f2f436, 0x0e323c3e, 0x85a1a425, 0xc9f1f839, 0x0d010c0d, 0xcfd3dc1f,
		0xc8d0d818, 0x0b23282b, 0x46626426, 0x4a72783a, 0x07232427, 0x0f232c2f, 0xc1f1f031, 0x42727032,
		0x42424002, 0xc4d0d414, 0x41414001, 0xc0c0c000, 0x43737033, 0x47636427, 0x8ca0ac2c, 0x8b83880b,
		0xc7f3f437, 0x8da1ac2d, 0x80808000, 0x0f131c1f, 0xcac2c80a, 0x0c202c2c, 0x8aa2a82a, 0x04303434,
		0xc2d2d012, 0x0b03080b, 0xcee2ec2e, 0xc9e1e829, 0x4d515c1d, 0x84909414, 0x08101818, 0xc8f0f838,
		0x47535417, 0x8ea2ac2e, 0x08000808, 0xc5c1c405, 0x03131013, 0xcdc1cc0d, 0x86828406, 0x89b1b839,
		0xcff3fc3f, 0x4d717c3d, 0xc1c1c001, 0x01313031, 0xc5f1f435, 0x8a82880a, 0x4a62682a, 0x81b1b031,
		0xc1d1d011, 0x00202020, 0xc7d3d417, 0x02020002, 0x02222022, 0x04000404, 0x48606828, 0x41717031,
		0x07030407, 0xcbd3d81b, 0x8d919c1d, 0x89919819, 0x41616021, 0x8eb2bc3e, 0xc6e2e426, 0x49515819,
		0xcdd1dc1d, 0x41515011, 0x80909010, 0xccd0dc1c, 0x8a92981a, 0x83a3a023, 0x8ba3a82b, 0xc0d0d010,
		0x81818001, 0x0f030c0f, 0x47434407, 0x0a12181a, 0xc3e3e023, 0xcce0ec2c, 0x8d818c0d, 0x8fb3bc3f,
		0x86929416, 0x4b73783b, 0x4c505c1c, 0x82a2a022, 0x81a1a021, 0x43636023, 0x03232023, 0x4d414c0d,
		0xc8c0c808, 0x8e929c1e, 0x8c909c1c, 0x0a32383a, 0x0c000c0c, 0x0e222c2e, 0x8ab2b83a, 0x4e626c2e,
		0x8f939c1f, 0x4a52581a, 0xc2f2f032, 0x82929012, 0xc3f3f033, 0x49414809, 0x48707838, 0xccc0cc0c,
		0x05111415, 0xcbf3f83b, 0x40707030, 0x45717435, 0x4f737c3f, 0x05313435, 0x00101010, 0x03030003,
		0x44606424, 0x4d616c2d, 0xc6c2c406, 0x44707434, 0xc5d1d415, 0x84b0b434, 0xcae2e82a, 0x09010809,
		0x46727436, 0x09111819, 0xcef2fc3e, 0x40404000, 0x02121012, 0xc0e0e020, 0x8db1bc3d, 0x05010405,
		0xcaf2f83a, 0x01010001, 0xc0f0f030, 0x0a22282a, 0x4e525c1e, 0x89a1a829, 0x46525416, 0x43434003,
		0x85818405, 0x04101414, 0x89818809, 0x8b93981b, 0x80b0b030, 0xc5e1e425, 0x48404808, 0x49717839,
		0x87939417, 0xccf0fc3c, 0x0e121c1e, 0x82828002, 0x01212021, 0x8c808c0c, 0x0b13181b, 0x4f535c1f,
		0x47737437, 0x44505414, 0x82b2b032, 0x0d111c1d, 0x05212425, 0x4f434c0f, 0x00000000, 0x46424406,
		0xcde1ec2d, 0x48505818, 0x42525012, 0xcbe3e82b, 0x4e727c3e, 0xcad2d81a, 0xc9c1c809, 0xcdf1fc3d,
		0x00303030, 0x85919415, 0x45616425, 0x0c303c3c, 0x86b2b436, 0xc4e0e424, 0x8bb3b83b, 0x4c707c3c,
		0x0e020c0e, 0x40505010, 0x09313839, 0x06222426, 0x02323032, 0x84808404, 0x49616829, 0x83939013,
		0x07333437, 0xc7e3e427, 0x04202424, 0x84a0a424, 0xcbc3c80b, 0x43535013, 0x0a02080a, 0x87838407,
		0xc9d1d819, 0x4c404c0c, 0x83838003, 0x8f838c0f, 0xcec2cc0e, 0x0b33383b, 0x4a42480a, 0x87b3b437
	);


	/************************** Constants for Key schedule ************************/

	//      KC0 = golden ratio; KCi = ROTL(KCi-1, 1)
	private $KC = array(
		0x9e3779b9, 0x3c6ef373, 0x78dde6e6, 0xf1bbcdcc, 0xe3779b99, 0xc6ef3733, 0x8dde6e67, 0x1bbcdccf,
		0x3779b99e, 0x6ef3733c, 0xdde6e678, 0xbbcdccf1, 0x779b99e3, 0xef3733c6, 0xde6e678d, 0xbcdccf1b
	);


	/**************************** Defining Endianness *****************************/
	// If endianness is not defined correctly, you must modify here.

	private $LITTLE     = false;
	private $BIG        = true;
	private $ENDIAN  = true;                // Java virtual machine uses big endian as a default
	//private Boolean ENDIAN     = LITTLE;


	/**************************** Constant Definitions ****************************/

	private $NoRounds   = 16;       // the number of rounds
	private $NoRoundKeys        = 32;       // the number of round-keys
	private $SeedBlockSize      = 16;       // block length in bytes
	private $SeedBlockLen       = 128;      // block length in bits

	//생성자
	public function __construct() {
	}
	//소멸자
	public function __destruct() {
	}
	/****************************** Common functions ******************************/

	//해당 자리수의 바이트를 얻는다
	private function GetB0($A){
		return 0x000000ff & $A;
	}
	private function GetB1($A){
		return 0x000000ff & ( $A >> 8 );
	}
	private function GetB2($A){
		return 0x000000ff & ( $A >> 16 );
	}
	private function GetB3($A){
		return 0x000000ff & ( $A >> 24 );
	}
	//----------------------------------------

	//엔디안 변환 (데이터의 순서 변환)
	private function EndianChange( $dws ) {
		return ( $dws >> 24 ) | ( $dws << 24 ) | ( ( $dws << 8 ) & 0x00ff0000 ) | ( ( $dws >> 8 ) & 0x0000ff00 );
	}


/***************************** SEED round function ****************************/

	public function SeedRound(
		&$L0, &$L1,             // [in, out] left-side variable at each round
		&$R0, &$R1,             // [in] right-side variable at each round
		$K = array()            // [in] round keys at each round
	)
	{
		$T0 = $R0 ^ $K[0];
		$T1 = $R1 ^ $K[1];
		$T1 ^= $T0;

		$T1 = $this->SS0[$this->GetB0($T1)] ^ $this->SS1[$this->GetB1($T1)] ^ $this->SS2[$this->GetB2($T1)] ^ $this->SS3[$this->GetB3($T1)];
		$T0 += $T1;
		$T0 = $this->ConvertInt($T0);

		$T0 = $this->SS0[$this->GetB0($T0)] ^ $this->SS1[$this->GetB1($T0)] ^ $this->SS2[$this->GetB2($T0)] ^ $this->SS3[$this->GetB3($T0)];
		$T1 += $T0;
		$T1 = $this->ConvertInt($T1);

		$T1 = $this->SS0[$this->GetB0($T1)] ^ $this->SS1[$this->GetB1($T1)] ^ $this->SS2[$this->GetB2($T1)] ^ $this->SS3[$this->GetB3($T1)];
		$T0 += $T1;
		$T0 = $this->ConvertInt($T0);

		$L0 ^= $T0;
		$L1 ^= $T1;
	}

	//추가된 함수 by mibany (2011-01-21)
	//PHP 에서는 float를 int로 강제로 형변환이 되지 않는다.
	//이사실을 전혀 몰랐던 나는 C++ 의 달인 Keige 님의 도움으로
	//PHP 로 구현하기 위한 핵심부분인 이 함수를 만들게 되었다.
	//문제는 고수가 아닌 나에게 이함수 구현이란 내게 어려운 일이었다.
	//혹시라도 이함수의 오류로 인해 Seed 암호화가 문제가 있을수도 있으니
	//고수분들의 도움이 절실하다.
	private function ConvertInt($float) {
		$IntMax = PHP_INT_MAX;
		$IntMin = ( PHP_INT_MAX * -1 ) -1;
		if(is_float($float) &&  $float < $IntMin ) {
			$division = floor($float / $IntMin );
			$n = ($division % 2 == 0)?0:$IntMin;
			if( $float < $IntMin ) $c = $float - ( $IntMin * $division ) - $n;
		}
		elseif(is_float($float) && $float > $IntMax) {
			$division = floor($float / $IntMax );
			$n = ($division % 2 == 0)?0:$IntMax;
			if( $float > $IntMax) $c = $float - ( $IntMax * $division ) - $n - 2;
		}
		else $c = $float;
		return $c;
	}

/************************** SEED encrtyption function *************************/

	public function SeedEncrypt(
		$pbData = array(),                  // [in] data to be encrypted
		$dIdx,                              // [in] data index to be encrypted
		$pdwRoundKey = array(),             // [in] round keys for encryption
		&$outData = array(),                // [out] out put data index
		$oIdx
	)
	{
		$L0 = 0x0;
		$L1 = 0x0;
		$R0 = 0x0;
		$R1 = 0x0;
		$K = array();
		$nCount = 0;

		// Set up input values for encryption
		$L0 = ( $pbData[$dIdx+0] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+1] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+2] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+3] & 0x000000ff );

		$L1 = ( $pbData[$dIdx+4] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^ ( $pbData[$dIdx+5] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^ ( $pbData[$dIdx+6] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^ ( $pbData[$dIdx+7] & 0x000000ff );

		$R0 = ( $pbData[$dIdx+8] & 0x000000ff );
		$R0 = ( $R0 <<8 ) ^ ( $pbData[$dIdx+9] & 0x000000ff );
		$R0 = ( $R0 <<8 ) ^ ( $pbData[$dIdx+10] & 0x000000ff );
		$R0 = ( $R0 <<8 ) ^ ( $pbData[$dIdx+11] & 0x000000ff );

		$R1 = ( $pbData[$dIdx+12] & 0x000000ff );
		$R1 = ( $R1 <<8 ) ^ ( $pbData[$dIdx+13] & 0x000000ff );
		$R1 = ( $R1 <<8 ) ^ ( $pbData[$dIdx+14] & 0x000000ff );
		$R1 = ( $R1 <<8 ) ^ ( $pbData[$dIdx+15] & 0x000000ff );

		// Reorder for little endian
		// Because java virtual machine use big endian order in default
		if (!$this->ENDIAN) {
			$this->EndianChange($L0);
			$this->EndianChange($L1);
			$this->EndianChange($R0);
			$this->EndianChange($R1);
		}

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   1 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   2 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   3 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   4 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   5 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   6 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   7 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   8 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   9 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  10 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  11 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  12 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  13 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  14 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  15 */

		$K[0] = $pdwRoundKey[$nCount++];
		$K[1] = $pdwRoundKey[$nCount++];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  16 */

		if (!$this->ENDIAN)  {
			$this->EndianChange($L0);
			$this->EndianChange($L1);
			$this->EndianChange($R0);
			$this->EndianChange($R1);
		}

		// Copying output values from last round to outData
		for ($i=0; $i<16; $i++) $outData[$oIdx+$i] = null;
		for ($i=0; $i<4; $i++)
		{
			$outData[$oIdx+$i] = ( ( ( $R0 ) >>( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+4+$i] = ( ( ( $R1 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+8+$i] = ( ( ( $L0 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+12+$i] = ( ( ( $L1 ) >> ( 8 * ( 3 - $i ) ) ) &0xff );
		}
	}


/************************** SEED decrtyption function *************************/

// Same as encrypt, except that round keys are applied in reverse order
	public function SeedDecrypt(
			$pbData = array(),                  // [in] encrypted data
			$dIdx,								// [in] data index to be decrypted
			$pdwRoundKey = array(),             // [in] round keys for decryption
			&$outData = array(),                // [out] data to be encrypted
			$oIdx
	)
	{
		$L0 = 0x0;
		$L1 = 0x0;
		$R0 = 0x0;
		$R1 = 0x0;
		$K = array();
		$nCount = 31;

		// Set up input values for decryption
		$L0 = ( $pbData[$dIdx+0] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+1] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+2] & 0x000000ff );
		$L0 = ( $L0 << 8 ) ^ ( $pbData[$dIdx+3] & 0x000000ff );

		$L1 = ( $pbData[$dIdx+4] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^(  $pbData[$dIdx+5] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^ ( $pbData[$dIdx+6] & 0x000000ff );
		$L1 = ( $L1 << 8 ) ^ ( $pbData[$dIdx+7] & 0x000000ff );

		$R0 = ( $pbData[$dIdx+8] & 0x000000ff );
		$R0 = ( $R0 << 8 ) ^ ( $pbData[$dIdx+9] & 0x000000ff );
		$R0 = ( $R0 << 8 ) ^ ( $pbData[$dIdx+10] & 0x000000ff );
		$R0 = ( $R0 << 8 ) ^ ( $pbData[$dIdx+11] & 0x000000ff );

		$R1 = ( $pbData[$dIdx+12] & 0x000000ff );
		$R1 = ( $R1 << 8 ) ^ ( $pbData[$dIdx+13] & 0x000000ff );
		$R1 = ( $R1 << 8 ) ^ ( $pbData[$dIdx+14] & 0x000000ff );
		$R1 = ( $R1 << 8 ) ^ ( $pbData[$dIdx+15] & 0x000000ff );

		// Reorder for little endian
		if (!$this->ENDIAN)  {
			$this->EndianChange($L0);
			$this->EndianChange($L1);
			$this->EndianChange($R0);
			$this->EndianChange($R1);
		}

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   1 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   2 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   3 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   4 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   5 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   6 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   7 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*   8 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*   9 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  10 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  11 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  12 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  13 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  14 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount--];
		$this->SeedRound($L0, $L1, $R0, $R1, $K); /*  15 */

		$K[1] = $pdwRoundKey[$nCount--];
		$K[0] = $pdwRoundKey[$nCount];
		$this->SeedRound($R0, $R1, $L0, $L1, $K); /*  16 */

		if (!$this->ENDIAN) {
			$this->EndianChange($L0);
			$this->EndianChange($L1);
			$this->EndianChange($R0);
			$this->EndianChange($R1);
		}

	// Copy output values from last round to outData
		for ($i=0; $i<16; $i++) $outData[$oIdx+$i] = null;
		for ($i=0; $i < 4; $i++)
		{
			$outData[$oIdx+$i] = ( ( ( $R0 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+4+$i] = ( ( ( $R1 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+8+$i] = ( ( ( $L0 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
			$outData[$oIdx+12+$i] = ( ( ( $L1 ) >> ( 8 * ( 3 - $i ) ) ) & 0xff );
		  }
	}


/************************ Functions for Key schedule **************************/

	private function EncRoundKeyUpdate0(&$K = array(), &$A, &$B, &$C, &$D, $Z)
	{
		$T0 = $A;

		$A = ( $A >> 8 & 0x00ffffff ) ^ ( $B << 24 );
		$B = ( $B >> 8 & 0x00ffffff ) ^ ( $T0 << 24 );

		$T00 = (int) $A + (int) $C - (int) $this->KC[$Z];
		$T00 = $this->ConvertInt($T00);

		$T11 = (int) $B + (int) $this->KC[$Z] - (int) $D;
		$T11 = $this->ConvertInt($T11);

		$K[0] = $this->SS0[$this->GetB0($T00)] ^ $this->SS1[$this->GetB1($T00)] ^ $this->SS2[$this->GetB2($T00)] ^ $this->SS3[$this->GetB3($T00)];
		$K[1] = $this->SS0[$this->GetB0($T11)] ^ $this->SS1[$this->GetB1($T11)] ^ $this->SS2[$this->GetB2($T11)] ^ $this->SS3[$this->GetB3($T11)];
	}

	private function EncRoundKeyUpdate1(&$K = array(), &$A, &$B, &$C, &$D, $Z)
	{
		$T0 = $C;
		$C = ( $C << 8 ) ^ ( $D >> 24   & 0x000000ff );
		$D = ( $D << 8 ) ^ ( $T0 >> 24   & 0x000000ff );

		$T00 = (int) $A + (int) $C - (int) $this->KC[$Z];
		$T00 = $this->ConvertInt($T00);

		$T11 = (int) $B + (int) $this->KC[$Z] - (int) $D;
		$T11 = $this->ConvertInt($T11);

		$K[0] = $this->SS0[$this->GetB0($T00)] ^ $this->SS1[$this->GetB1($T00)] ^ $this->SS2[$this->GetB2($T00)] ^ $this->SS3[$this->GetB3($T00)];
		$K[1] = $this->SS0[$this->GetB0($T11)] ^ $this->SS1[$this->GetB1($T11)] ^ $this->SS2[$this->GetB2($T11)] ^ $this->SS3[$this->GetB3($T11)];
	}

	/******************************** Key Schedule ********************************/
	public function SeedRoundKey(
			&$pdwRoundKey = array(),            // [out] round keys for encryption or decryption
			$pbUserKey = array()                // [in] secret user key
	)
	{
		$K = array();
		$nCount = 2;

		// Set up input values for Key Schedule
		$A = ( $pbUserKey[0] & 0x000000ff );
		$A = ( $A << 8 ) ^ ( $pbUserKey[1] & 0x000000ff );
		$A = ( $A << 8 ) ^ ( $pbUserKey[2] & 0x000000ff );
		$A = ( $A << 8 ) ^ ( $pbUserKey[3] & 0x000000ff );

		$B = ( $pbUserKey[4] & 0x000000ff );
		$B = ( $B<<8 ) ^ ( $pbUserKey[5] & 0x000000ff );
		$B = ( $B<<8 ) ^ ( $pbUserKey[6] & 0x000000ff );
		$B = ( $B<<8 ) ^ ( $pbUserKey[7] & 0x000000ff );

		$C = ( $pbUserKey[8] & 0x000000ff );
		$C = ( $C << 8 ) ^ ( $pbUserKey[9] & 0x000000ff );
		$C = ( $C << 8 ) ^ ( $pbUserKey[10] & 0x000000ff );
		$C = ( $C << 8 ) ^ ( $pbUserKey[11] & 0x000000ff );

		$D = ( $pbUserKey[12] & 0x000000ff );
		$D = ( $D << 8 ) ^ ($pbUserKey[13] & 0x000000ff );
		$D = ( $D << 8 ) ^ ( $pbUserKey[14] & 0x000000ff );
		$D = ( $D << 8 ) ^ ( $pbUserKey[15] & 0x000000ff );

		// reorder for little endian
		if (!$this->ENDIAN)  {
			$A = $this->EndianChange($A);
			$B = $this->EndianChange($B);
			$C = $this->EndianChange($C);
			$D = $this->EndianChange($D);
		}

		$T0 = (int) $A + (int) $C - (int) $this->KC[0];
		$T0 = $this->ConvertInt($T0);

		$T1 = (int) $B - (int) $D + (int) $this->KC[0];
		$T1 = $this->ConvertInt($T1);

		$pdwRoundKey[0] = $this->SS0[$this->GetB0($T0)] ^ $this->SS1[$this->GetB1($T0)] ^ $this->SS2[$this->GetB2($T0)] ^ $this->SS3[$this->GetB3($T0)];
		$pdwRoundKey[1] = $this->SS0[$this->GetB0($T1)] ^ $this->SS1[$this->GetB1($T1)] ^ $this->SS2[$this->GetB2($T1)] ^ $this->SS3[$this->GetB3($T1)];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 1 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 2 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 3 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 4 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 5 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 6 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 7 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 8 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 9 );
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 10);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 11);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 12);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 13);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate1($K, $A, $B, $C, $D, 14);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];

		$this->EncRoundKeyUpdate0($K, $A, $B, $C, $D, 15);
		$pdwRoundKey[$nCount++] = $K[0];
		$pdwRoundKey[$nCount++] = $K[1];
	}

	public function Encrypt($pbData, $pdwRoundKey, $cbcPad)
	{
		$arrSrc = array();
		$strOut = "";
		$nOutSize = strlen($pbData);
		
		for($i=0; $i < $nOutSize; $i++) $arrSrc[$i] = ord($pbData[$i]);

		if ($cbcPad)
		{
			$plen = 16 - $nOutSize % 16;
			
			 for ($i = 0; $i < $plen; $i++) $arrSrc[$nOutSize+$i] = $plen;
			 
			 $nOutSize += $plen;
		}
		
		if (0 != $nOutSize % 16) return $strOut;
		
	    // CBC 모드 초기값 IV
	    $PrevData = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	    
	    $outData = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	    
	    for ($i = 0; $i < $nOutSize; $i += 16)
	    {
	        if ($cbcPad)
	        {
	        	for ($j = 0; $j < 16; $j++) $arrSrc[$i + $j] = $arrSrc[$i + $j] ^ $PrevData[$j];
	        }
	        
	        $this->SeedEncrypt( $arrSrc, $i, $pdwRoundKey, $outData, 0 );
	        
	        if ($cbcPad)
	        {
	        	$PrevData = $outData;
	        }
	        
	        for ($j = 0; $j < 16; $j++)
	        {
	        	$strOut .= pack ("C",$outData[$j]);
	        }
	    }
	    return $strOut;
	}

    public function Decrypt($ebData, $pdwRoundKey, $cbcPad)
	{
	    $arrSrc = array();
	    $strOut = "";
	    $nOutSize = strlen($ebData);
	    
	    for($i=0; $i < $nOutSize; $i++) $arrSrc[$i] = ord($ebData[$i]);

	    if (0 != $nOutSize % 16) return $strOut;
	
	    $PrevData = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	    
	    $outData = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	
	    for ($i = 0; $i < $nOutSize; $i += 16)
	    {
	        $this->SeedDecrypt( $arrSrc, $i, $pdwRoundKey, $outData ,0);
	
	        if ($cbcPad)
	        {
	        	for ($j = 0; $j < 16; $j++)
	        	{
	        		$outData[$j] = $outData[$j] ^ $PrevData[$j];
	        		$PrevData[$j] = $arrSrc[$i+$j];
	        	}
	        	
	        }

	        for ($j = 0; $j < 16; $j++)
	        {
	        	$strOut .= pack ("C",$outData[$j]);
	        }	        
	    }
	
	    if ($cbcPad)
	    {
	        $plen = $outData[15];
	        $strOut = substr($strOut,0,$nOutSize-$plen);
	    }

	    return $strOut;
	}
}
?>