<?php
    define(call_url,"GET /ssltest/test_ok.jsp HTTP/1.0\r\n");


    function reqPage($sData){
        $sock=conSock($sData,call_url);
        if($sock){
            $sBody=getBody($sock);
        }else{
            $sBody="<HTML><HEAD><TITLE> Allat Pay Connection Test Fail</TITLE></HEAD>
                    <BODY>AllatPay Server와 연결 테스트가 실패 하였습니다.</BODY></HTML>";
        }
        return $sBody;
    }

    function getBody($sockv){
        while(!feof($sockv)){
            $headerv=fgets($sockv,4096);
            if($headerv=="\r\n"){
                break;
            }
        }
        while(!feof($sockv)){
            $sBody.=fgets($sockv,4096);
        }
        return $sBody;
    }

    function conSock($sData,$sCallurl){
        $sock = @fsockopen("ssl://tx.allatpay.com",443, $errno, $errstr, 30);
        if($sock){
            fwrite($sock, $sCallurl);
            fwrite($sock, "Host: tx.allatpay.com:443\r\n");
            fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($sock, "Content-length: " . strlen($sData) . "\r\n");
            fwrite($sock, "Accept: */*\r\n");
            fwrite($sock, "\r\n");
            fwrite($sock, "$sData\r\n");
            fwrite($sock, "\r\n");
        }
        return $sock;
    }

    $testPage=reqPage(" ");
    echo $testPage;
?>
