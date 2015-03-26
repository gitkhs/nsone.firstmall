<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*-----------------------------------------------------------------------------------------------------------------------
File Name : cookieSecure.php
Engineer : 박명주
Date : 2005-10-24
Version : 1.2
Comment
본 파일은 고전대치 암호화 중 Caesar 방식을 응용 하여 개발하였음.
총 두개의 함수로 이루어져 있으며, cookieEncode는 인코딩 함수이고, cookieDecode는 디코딩 함수 임.
각 함수에 있는 $arrSecureKeys 변수는 배열변수 이며
해당 변수의 내용값은 변경 하여도 무난 하지만 두 함수의 키값은 동일 해야한다. (이유는 고전대치 암호화에 대해 알아보라)

키값의 정보는 각 서비스 마다 달라야 한다.
따라서 각 서비스 개발자는 키 조합을 달리해서 적용 하기 바란다.
본 키값의 정보는 웹메일 서비스용 이므로 사용을 금지함.

쿠키에 저장되는 값의 일치성을 떨어뜨리기 위해 16진수 16Byte 해쉬를 사용함.	- 1.2 추가내용 -
-----------------------------------------------------------------------------------------------------------------------*/

global $hex16HashKey;

$hex16HashKey = "gabiataesachi";		// 해쉬를 위해 사용될 키값... 각 함수 내에서는 global로 선언하여 사용할 것

function getDiv($divLen)
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : getDiv
	Parameter	: $divLen - random 값으로 키를 생성할 바이트수
	Engineer : 박명주
	Date : 2005-11-02
	Version : 1.0
	-----------------------------------------------------------------------------------------------------------------------*/
	$tmp = '';
	while ($divLen-- > 0)		// 파라미터로 넘어온 바이트 수
	{
		$tmp .= chr(mt_rand() & 0xff);		// 0xff 10진수 255 까지의 랜덤 수로 부터 키가될 문자열을 생성한다.
	}
	return $tmp;
}

function encHash($strTarget, $hashKey, $divLen = 16)		// 추후 쿠키변조시 Byte 수를 증가 시키기위해 divLen 을 파라미터화
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : encHash
	Parameter	: $strTarget - 해싱할 값
						$password - 해싱시 사용될 디코드용 키 값
	Engineer : 박명주
	Date : 2005-11-02
	Version : 1.0
	Comment
	XOR 연산을 사용한 이유
	비트연산을 수행해서 각 변수에 입력된 값을 Swap 하기 위함.
	궁금한 점은 배타적 논리연산에 대해서 알아볼것.
	-----------------------------------------------------------------------------------------------------------------------*/
	$strTarget .= "\x13";
	$n = strlen($strTarget);

	if ($n % 16)		// 16의 배수로 문자열을 만들기 위해 해싱될 값의 길이에 16으로 나누어 나머지가 있으면
	{
		$strTarget .= str_repeat("\0", 16 - ($n % 16));		// 해싱될 값의 끝 부분을 다음 16배수의 문자열이 되도록 NULL을 채운다.
	}
	// 16배수로 만드는 이유는 16진수 16Byte 해싱을 위해

	$i = 0;
	$strEncode = getDiv($divLen);		// 해싱될 키 값
	$tmp = substr($hashKey ^ $strEncode, 0, 512);		// 파라미터로 입력된 해싱 디코드 키 값과 해싱될 키값을 비트연산자 XOR 로 계산후 512 바이트로 자른다. 16진수 키값 연산완료

	while ($i < $n)		// 입력된 해싱될 값의 길이만큼 루프
	{
		$hashBlock = substr($strTarget, $i, 16) ^ pack('H*', md5($tmp));		// 해쉬될 값을 16바이트만큼 자른 후 16진수 키값의 Binary 값과 XOR 연산을 하여 16바이트 Swap을 한다.
		$strEncode .= $hashBlock;
		$tmp = substr($hashBlock . $iv, 0, 512) ^ $hashKey;		// Swap된 16바이트 문자열과 16진수 키값의 나열된 문자열을 512 바이트 만큼 자르고 입력된 해싱 디코드용 키값과 XOR 연산
		$i += 16;		// 입력된 해싱값중 16Byte가 해쉬 되었으므로 루프값 16증가
	}
	return base64_encode($strEncode);		// 해쉬된 문자열을 base64로 인코딩
}

function decHash($strEncode, $hashKey, $divLen = 16)		// 추후 쿠키변조시 Byte 수를 증가 시키기위해 divLen 을 파라미터화
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : decHash
	Parameter	: $strEncode - 디코드할 값
						$hashKey - 해싱시 사용했던 디코드용 키 값
	Engineer : 박명주
	Date : 2005-11-02
	Version : 1.0
	Comment
	언제나 그렇듯 디코딩은 인코딩의 역순이다.
	-----------------------------------------------------------------------------------------------------------------------*/
	$strEncode = base64_decode($strEncode);		// 입력된 문자열을 base64로 디코딩
	$n = strlen($strEncode);		// 입력된 문자열의 전체 길이를 구한다.

	$i = $divLen;
	$strDecode = '';
	$tmp = substr($hashKey ^ substr($strEncode, 0, $divLen), 0, 512);		// 입력된 문자열의 앞열 16Byte와 키값을 XOR 연산하여 결과값을 512 Byte 만큼 자른다. (해쉬한 키값을 가져온다.)

	while ($i < $n)
	{
		$hashBlock = substr($strEncode, $i, 16);		// 입력된 문자열의 앞 16Byte 이후 부터 16Byte씩 자른다.
		$strDecode .= $hashBlock ^ pack('H*', md5($tmp));		// 입력된 문자열과 키값의 Binary 값과 XOR 연산
		$tmp = substr($hashBlock . $iv, 0, 512) ^ $hashKey;		// 다음 16Byte에 대한 해쉬 키 값을 가져온다.
		$i += 16;
	}
	return preg_replace('/\\x13\\x00*$/', '', $strDecode);		// 해쉬할때 입력했던 Byte 맞추기용 NULL과 Hex 13 번을 지운다.
}

function chgKeyValue($key, $serviceValue)
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : cookieEncode
	Parameter	: $key - 변조될 키 변수 (Array)
					  $serviceValue - 서비스마다 키 값을 변조 해야하므로 변조 범위를 입력받는 정수형 변수 (1~94) - Version 1.1 추가사항
	Engineer : 박명주
	Date : 2005-10-25
	Version : 1.0
	-----------------------------------------------------------------------------------------------------------------------*/

	$arrValue = array_values($key);		// 키에 저장된 값들을 임시 배열변수에 저장

	for($i=0; $i<count($arrValue); $i++)
	{
		$searchKeys = array_keys($key, $arrValue[$i]);		// 해당 키 값으로 암호화 키에 지정된 배열키 값을 읽어옴.
		$chgValue = $arrValue[$i]+$serviceValue;			// 키변조를 위해 불러온 배열값을 변조할 만큼 더해줌.

		if ($chgValue > 94)		// 변조된 값이 94보다 클 경우
		{
			$chgValue = $chgValue - 95;	// 94+1 을 빼줌
		}

		if (strlen($chgValue) < 2)		// 해당 문자열 길이가 2보다 작으면 암호화 문자열 체계를 맞추기위해 2자릿수로 변경
		{
			$chgValue = "0" . $chgValue;
		}

		$tmpSecureKeys[$searchKeys[0]] = $chgValue;		// 임시 암호화 키 배열에 해당 변조된 값을 저장함 (기존 암호화 키배열에 직접 값을 변경하면 변경된 값과 기존값이 중첩될수 있으므로)
	}

	if (is_array($tmpSecureKeys))		// 임시 암호화 키 배열이 정상적인 배열일 경우
	{
		$key = $tmpSecureKeys;		// 기존 암호화 키 배열에 저장
	}
	else
	{
		$key = false;
	}

	return $key;
}

function cookieEncode($strCookie, $serviceValue)
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : cookieEncode
	Parameter	: $strCookie - 인코딩될 문자열 (String)
					  $serviceValue - 서비스마다 키 값을 변조 해야하므로 변조 범위를 입력받는 정수형 변수 (1~94) - Version 1.1 추가사항
	Engineer : 박명주
	Date : 2005-10-24
	Version : 1.0
				  1.1
				  1.2
	-----------------------------------------------------------------------------------------------------------------------*/

	global $hex16HashKey;

	$arrSecureKeys = array(
	ord("~")=>"00", ord("!")=>"01", ord("@")=>"02", ord("#")=>"03", ord("$")=>"04", ord("%")=>"05", ord("^")=>"06", ord("&")=>"07", ord("*")=>"08", ord("(")=>"09",
	ord(")")=>"10", ord("_")=>"11", ord("+")=>"12", ord("|")=>"13", ord("`")=>"14", ord("1")=>"15", ord("2")=>"16", ord("3")=>"17", ord("4")=>"18", ord("5")=>"19",
	ord("6")=>"20", ord("7")=>"21", ord("8")=>"22", ord("9")=>"23", ord("0")=>"24", ord("-")=>"25", ord("=")=>"26", ord("\\")=>"27", ord("q")=>"28", ord("w")=>"29",
	ord("e")=>"30", ord("r")=>"31", ord("t")=>"32", ord("y")=>"33", ord("u")=>"34", ord("i")=>"35", ord("o")=>"36", ord("p")=>"37", ord("[")=>"38", ord("]")=>"39",
	ord("a")=>"40", ord("s")=>"41", ord("d")=>"42", ord("f")=>"43", ord("g")=>"44", ord("h")=>"45", ord("j")=>"46", ord("k")=>"47", ord("l")=>"48", ord(";")=>"49",
	ord("'")=>"50", ord("z")=>"51", ord("x")=>"52", ord("c")=>"53", ord("v")=>"54", ord("b")=>"55", ord("n")=>"56", ord("m")=>"57", ord(",")=>"58", ord(".")=>"59",
	ord("/")=>"60", ord("Q")=>"61", ord("W")=>"62", ord("E")=>"63", ord("R")=>"64", ord("T")=>"65", ord("Y")=>"66", ord("U")=>"67", ord("I")=>"68", ord("O")=>"69",
	ord("P")=>"70", ord("{")=>"71", ord("}")=>"72", ord("A")=>"73", ord("S")=>"74", ord("D")=>"75", ord("F")=>"76", ord("G")=>"77", ord("H")=>"78", ord("J")=>"79",
	ord("K")=>"80", ord("L")=>"81", ord(":")=>"82", ord("\"")=>"83", ord("Z")=>"84", ord("X")=>"85", ord("C")=>"86", ord("V")=>"87", ord("B")=>"88", ord("N")=>"89",
	ord("M")=>"90", ord("<")=>"91", ord(">")=>"92", ord("?")=>"93", ord(" ")=>"94");		// 인증키 값

	if (!$serviceValue || $serviceValue > 94)
	{
		return false;
	}
	else
	{
		$arrSecureKeys = chgKeyValue($arrSecureKeys, $serviceValue);		// 키 값 변조
		if (!$arrSecureKeys)
		{
			return false;
		}
	}

	$strCookie = urlencode($strCookie);		// 유니코드 처리를 위해 urlencode
	if (strlen(trim($strCookie)) < 1)	 // 파리미터로 넘어온 문자열이 없으면
	{
		return false;
	}
	else
	{
		for($i=0; $i<strlen($strCookie); $i++)
		{
			$subtmp = substr($strCookie, $i, 1);		// 입력된 문자열을 한글자씩 분리
			$retValue .= $arrSecureKeys[ord($subtmp)];		// 입력된 값을 Key로 사용하는 배열의 값을 대입
		}
	}

	unset($arrSecureKeys);		// 인코딩 완료 후 키 배열을 초기화 해주는 센스...

	if (strlen(trim($retValue)) < 1)		// 인코딩된 값이 없을경우
	{
		return false;
	}
	else
	{
		$chkLen = strlen($strCookie) * 2;		// 인코딩된 문자열은 항상 원래 문자열길이의 두배
		if ($chkLen == strlen($retValue))		// 인코딩된 문자열의 길이와 원래 문자열길이의 두배와 같아야만 인코딩완료
		{
			return encHash($retValue, $hex16HashKey);		// 인코딩 완료된 배열값을 해쉬하여 리턴 - 1.2 추가사항
		}
		else
		{
			return false;
		}
	}
}

function cookieDecode($encCookie, $serviceValue)
{
	/*-----------------------------------------------------------------------------------------------------------------------
	Function Name : cookieDecode
	Parameter	: $encCookie - 디코딩될 문자열 (String)
					  $serviceValue - 서비스마다 키 값을 변조 해야하므로 변조 범위를 입력받는 정수형 변수 (1~94) - Version 1.1 추가사항
	Engineer : 박명주
	Date : 2005-10-24
	Version : 1.0
				  1.1
	-----------------------------------------------------------------------------------------------------------------------*/

	global $hex16HashKey;

	$arrSecureKeys = array(
	ord("~")=>"00", ord("!")=>"01", ord("@")=>"02", ord("#")=>"03", ord("$")=>"04", ord("%")=>"05", ord("^")=>"06", ord("&")=>"07", ord("*")=>"08", ord("(")=>"09",
	ord(")")=>"10", ord("_")=>"11", ord("+")=>"12", ord("|")=>"13", ord("`")=>"14", ord("1")=>"15", ord("2")=>"16", ord("3")=>"17", ord("4")=>"18", ord("5")=>"19",
	ord("6")=>"20", ord("7")=>"21", ord("8")=>"22", ord("9")=>"23", ord("0")=>"24", ord("-")=>"25", ord("=")=>"26", ord("\\")=>"27", ord("q")=>"28", ord("w")=>"29",
	ord("e")=>"30", ord("r")=>"31", ord("t")=>"32", ord("y")=>"33", ord("u")=>"34", ord("i")=>"35", ord("o")=>"36", ord("p")=>"37", ord("[")=>"38", ord("]")=>"39",
	ord("a")=>"40", ord("s")=>"41", ord("d")=>"42", ord("f")=>"43", ord("g")=>"44", ord("h")=>"45", ord("j")=>"46", ord("k")=>"47", ord("l")=>"48", ord(";")=>"49",
	ord("'")=>"50", ord("z")=>"51", ord("x")=>"52", ord("c")=>"53", ord("v")=>"54", ord("b")=>"55", ord("n")=>"56", ord("m")=>"57", ord(",")=>"58", ord(".")=>"59",
	ord("/")=>"60", ord("Q")=>"61", ord("W")=>"62", ord("E")=>"63", ord("R")=>"64", ord("T")=>"65", ord("Y")=>"66", ord("U")=>"67", ord("I")=>"68", ord("O")=>"69",
	ord("P")=>"70", ord("{")=>"71", ord("}")=>"72", ord("A")=>"73", ord("S")=>"74", ord("D")=>"75", ord("F")=>"76", ord("G")=>"77", ord("H")=>"78", ord("J")=>"79",
	ord("K")=>"80", ord("L")=>"81", ord(":")=>"82", ord("\"")=>"83", ord("Z")=>"84", ord("X")=>"85", ord("C")=>"86", ord("V")=>"87", ord("B")=>"88", ord("N")=>"89",
	ord("M")=>"90", ord("<")=>"91", ord(">")=>"92", ord("?")=>"93", ord(" ")=>"94");		// 인증키 값

	if (!$serviceValue || $serviceValue > 94)
	{
		return false;
	}
	else
	{
		$arrSecureKeys = chgKeyValue($arrSecureKeys, $serviceValue);		// 키 값 변조
		if (!$arrSecureKeys)
		{
			return false;
		}
	}

	if (strlen(trim($encCookie)) < 1)	 // 파리미터로 넘어온 문자열이 없으면
	{
		return false;
	}
	else
	{
		$encCookie = decHash($encCookie, $hex16HashKey);		// 디코딩될 문자열값이 해쉬되어 있으므로 해쉬된 값을 디코딩 해준다. - 1.2 추가사항
		for($i=0; $i<strlen($encCookie); $i++)
		{
			$subtmp = substr($encCookie, $i*2, 2);		// 문자열을 두글자씩 분리 (인코딩시에 한 문자를 두개의 문자열로 변경 하므로)
			$searchKeys = array_keys($arrSecureKeys, $subtmp);		// 분리된 값에 대한 Key를 찾는다.

			if (strlen(trim($searchKeys[0])) > 0)		// Key가 존재 할 경우
			{
				$retValue .= chr($searchKeys[0]);		// 디코딩하여 해당 값을 대입
			}
		}
	}

	unset($arrSecureKeys);		// 디코딩 완료 후 키 배열을 초기화 해주는 센스...

	if (strlen(trim($retValue)) < 1)
	{
		return false;
	}
	else
	{
		$chkLen = strlen($encCookie) / 2;		// 디코딩된 문자열은 인코딩 문자열의 1/2
		if ($chkLen == strlen($retValue))			// 디코딩된 문자열과 인코딩 문자열의 1/2 이 같아야 디코딩 완료
		{
			$retValue = urldecode($retValue);		// 유니코드 처리를 위해 urldecode
			return $retValue;
		}
		else
		{
			return false;
		}
	}
}
	
// END
/* End of file cookieSecure_helper.php */
/* Location: ./app/helpers/cookieSecure_helper.php */