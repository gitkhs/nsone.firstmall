<?
function write_log ($strLogMsg)
{
	$LOG_HOME_DIR	= "";//로그를 남길경우 LOG_HOME_DIR을 "c:/log/" 와 같이 '/'를 경로 구분자로 하여 '/'로 끝나도록 로그를 남길 폴더를 지정해야 함
	if (empty($LOG_HOME_DIR) || "/" != substr($LOG_HOME_DIR,(strlen($LOG_HOME_DIR)-1))) return;
	
	$curr_time_14 = strftime("%Y%m%d%H:%M:%S");

	$strLogFile =	$LOG_HOME_DIR. "kspay_" . substr($curr_time_14,0,8) . ".log";

	$strRecord = "[" . substr($curr_time_14,8) . "]" . $strLogMsg . "\n";

	$fp	= fopen($strLogFile, "a");
	fwrite($fp,	$strRecord);
	fclose($fp);
}

class KSPayWebHost
{
	var $KSPAY_WEBHOST_URI	= "/store/KSPayFlashV1.3/web_host/recv_post.jsp";
	var $KSPAY_WEBHOST_HOST	= "kspay.ksnet.to";
	var $KSPAY_WEBHOST_IP	= "210.181.28.137";

	//var $KSPAY_WEBHOST_HOST	= "210.181.28.116";
	//var $KSPAY_WEBHOST_IP	= "210.181.28.116";

	var $payKey		;
	var $rparams	;
	var $mtype		;

	var $rnames		= array();
	var $rvalues	= array();

	var $DEFAULT_DELIM = "`";
    var $DEFAULT_RPARAMS	= "authyn`trno`trddt`trdtm`amt`authno`msg1`msg2`ordno`isscd`aqucd`result";
    // authyn : O/X 상태
    // trno   : KSNET거래번호(영수증 및 취소 등 결제데이터용 KEY
    // trddt  : 거래일자(YYYYMMDD)
    // trdtm  : 거래시간(hhmmss)
    // amt    : 금액
    // authno : 승인번호(신용카드:결제성공시), 에러코드(신용카드:승인거절시), 은행코드(가상계좌,계좌이체)
    // ordno  : 주문번호
    // isscd  : 발급사코드(신용카드), 가상계좌번호(가상계좌) ,기타결제수단의 경우 의미없음
    // aqucd  : 매입사코드(신용카드)
    // result : 승인구분

	public function __construct($_payKey, $_rparams)
	{
		$this->payKey		= $_payKey;

		if (empty($_rparams) || false === strpos($_rparams,$this->DEFAULT_DELIM))
		{
			$this->rparams	= $this->DEFAULT_RPARAMS;
		}else
		{
			$this->rparams	= $_rparams;
		}

		$this->rnames	= split($this->DEFAULT_DELIM, $this->rparams);
	}

	// mobile일 경우 URI 변경.
	public function kspay_set_mobile()
	{
		$this->KSPAY_WEBHOST_URI	= "/store/mb2/web_host/recv_post.jsp";
	}

	public function kspay_get_value($pname)
	{
		if (empty($pname) || !is_array($this->rnames) || !is_array($this->rvalues) || count($this->rnames) != count($this->rvalues)) return null;
		
		return $this->rvalues[$pname];
	}

	public function kspay_send_msg($_mtype)
	{
		$this->mtype = $_mtype;
		$rmsg = $this->send_url();

		if (false === strpos($rmsg,$this->DEFAULT_DELIM)) return false;

		$tmpvals = split($this->DEFAULT_DELIM, $rmsg);
		if (count($this->rnames) < count($tmpvals))
		{
			for($i=0; $i<count($this->rnames); $i++)
			{
				$this->rvalues[$this->rnames[$i]] = $tmpvals[$i+1];
			}
			return true;
		}
	}

	function send_url()
    {
		$post_msg = "sndCommConId=" . $this->payKey . "&sndActionType=" . $this->mtype . "&sndRpyParams=" . urlencode($this->rparams);

		//$_my_url = "http://";
		//if (false === stripos($_SERVER['SERVER_PROTOCOL'], "HTTP/")) $_my_url = "https://";
		//$_my_url .= $_SERVER['SERVER_NAME'];
		//if (!empty($_SERVER['SERVER_PORT']) && 80 != $_SERVER['SERVER_PORT'] && 443 != $_SERVER['SERVER_PORT']) $_my_url .= ":" . $_SERVER['SERVER_PORT'];		
		//$_my_url .= $_SERVER['PHP_SELF'];

		$req_msg  = "POST " . $this->KSPAY_WEBHOST_URI . " HTTP/1.0\r\n";
		$req_msg .= "Host: " . $this->KSPAY_WEBHOST_HOST . "\r\n";
		//$req_msg .= "Referer: " . $_my_url . "\r\n";
		$req_msg .= "Accept-Language: ko\r\n";
		$req_msg .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n";
		$req_msg .= "Content-type: application/x-www-form-urlencoded\r\n";
		$req_msg .= "Content-length: ".strlen($post_msg)."\r\n";
		$req_msg .= "Connection: close\r\n";
		$req_msg .= "\r\n";
		$req_msg .= $post_msg;

		$kspay_ipaddr = gethostbyname($this->KSPAY_WEBHOST_HOST);
		$kspay_port   = 80;
write_log("send_url:send(" . $this->payKey . ",".$kspay_ipaddr.",".$kspay_port.")=[".$post_msg."]");
		if ($kspay_ipaddr == $this->KSPAY_WEBHOST_HOST)
		{
			$kspay_ipaddr = $this->KSPAY_WEBHOST_IP;
write_log("CHECK: gethostbyname(" . $this->KSPAY_WEBHOST_HOST . "):X DEFALUT IP=[".$this->KSPAY_WEBHOST_IP."]");
		}

		$fp_socket = fsockopen($kspay_ipaddr, $kspay_port, $errno, $errstr, 60);
		if($fp_socket) {
			fwrite($fp_socket,$req_msg, strlen($req_msg));
			fflush($fp_socket);
			while(!feof($fp_socket)) {
				$rpy_msg .= fread($fp_socket, 8192);
			}
		}
		fclose($fp_socket);

		$rtn_msg = "";
		$rpos = strpos($rpy_msg,"\r\n\r\n");

		if ($rpos !== false) $rtn_msg = substr($rpy_msg, $rpos+4);

write_log("send_url:recv(" . $this->payKey . ",".$kspay_ipaddr.",".$kspay_port.")=[".$rtn_msg."]");

		return $rtn_msg;
	}
}
?>