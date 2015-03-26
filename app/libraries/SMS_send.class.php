<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/libraries/class.socket.php';

class SMS_SEND extends CI_Model {

	var $from;
	var $to;

	var $groupkey;

	var $sms_account;
	var $sms_password;

	var $gabiaUrl			= 'http://firstmall.kr';
	var $gabiaUrlPath		= '/payment_firstmall/process.php';
	var $smsServer			= 'sms.firstmall.kr';
	var $smsPort			= '5000';
	var $smsTimeout			= 5;
	var $smsStatus			= "1";
	var $sms_key			= "GS";
	var $smsEndOfCommand	= "\n";
	var $SMS_Q_TYPE			= "0";
	var $cTrandate			= "0";	// 예약발송
	var $gsinfo				= array();
	var $type;
	var $msg;
	var $limit;
	var $client;


	public function SMS_SEND()
	{
		$CI =& get_instance();
		$CI->load->helper('readurl');

		$this->gsinfo = $CI->config_system['service'];

		$bits = parse_url($this->gabiaUrl);
		$host = $bits['host'];
		$port = isset($bits['port']) ? $bits['port'] : 80;
		$path = isset($bits['path']) ? $bits['path'] : '/';

		$this->client	= new HttpClient($host, $port);

		$data['mode'	]	= 'getSmsInfo';
		$data['params'	]	= makeEncriptParam("mallid=" . $CI->config_system['service']['cid'] . "&sms_id=" . $CI->config_system['service']['sms_id']);

		$sms_pw				= NULL;
		if ($res=readurl($this->gabiaUrl.$this->gabiaUrlPath,$data))
		{
			$sms_pw	= $res;
		}
		$this->sms_account	= $CI->config_system['service']['sms_id'];
		$this->sms_password	= $sms_pw;


		### LOG
		if(!isset($row["gk"])) $row["gk"] = 0;
		$this->groupkey = $row["gk"]+1;

		if(!$this->sms_account || !$this->sms_password)
		{
			$this->error("SMS 서비스 가입을 하셔야합니다");
			return;
		}
		$this->getLimit();

		/*
		if (!$this->connect()) {
			$this->error("Connection server failed"); return;
		}
		*/
	}

	public function getConfrim ()
	{
		global $_service_;

		$CI =& get_instance();

		$confirmnum			= md5($_POST['confirmnum']);
		$params[]			= "mallid=" . $CI->config_system['service']['cid'];
		$params[]			= "sms_id=" . $CI->config_system['service']['sms_id'];
		$params[]			= "confirmnum=" . $confirmnum;
		$data['mode'	]	= 'confirmMall';
		$data['params'	]	= makeEncriptParam(implode("&", $params));

		if ($res=readurl($this->gabiaUrl.$this->gabiaUrlPath,$data))
		{
			return $res;
		}
	}

	public function connect()
	{
		$ErrNo	= isset($this->ErrNo) ? isset($this->ErrNo) : "";
		$ErrMsg = isset($this->ErrMsg) ? isset($this->ErrMsg) : "";
		if ($this->Socket = fsockopen($this->smsServer, $this->smsPort, $ErrNo, $ErrMsg, $this->smsTimeout))
		{
			return true;
		}
		return false;
	}

	public function error($msg)
	{
		$this->error = true;
		$this->msg = $msg;
	//$this->disconnect();
	}

	public function disconnect()
	{
		if ($this->Socket)
		{
			fclose($this->Socket);
		}
	}

	/**
	 * 메세지를 전송합니다. $this->to 부분에 값이 있어야 합니다. 값의 양식은..
	 * $this->to = array(array("phone"=>"010-2222-2222", "mid"=>"bahamut"),array("phone"=>"010-2222-2222", "mid"=>"bahamut"));
	 * --> 요런형식...그러니까 print_r로 표현하면...
	 * array(
	 *		[0] = array(
	 *					"phone" = "010-2222-2222",
	 *					"mid" = "bahmut"
	 *					),
	 *		[1] = array(
	 *					"phone" = "010-2222-2222",
	 *					 "mid" = "bahmut"
	 *					),
	 * )
	 * 이런 형식입니다. ^^* 어떻게 집어넣는지는 자기 스킬에 따라...젠ㅋ장ㅋ 저는 참고로 & 이용해서 넣었츰 ㅠ_ㅜ
	 * @param string $msg
	 * @return bool
	 */
	public function send($msg)
	{

		$CI =& get_instance();

		if (!is_array($msg))
		{
			$tmp = array();
			$tmp[0] = $msg;
			$msg = $tmp;
		}
		if (!$this->sms_password)
		{
			$this->error("접속정보가 정상적이지 않습니다. 확인하시기 바랍니다.");
			return false;
		}

		$this->from = str_replace("-","",$this->from);
		$this->multiple = count($msg);

		if (!$this->multiple || $this->multiple>2)
		{
			$this->error("메세지에 문제가 있습니다. 관리자에게 문의하세요"); return false;
		}

		if (!is_array($this->to)) $this->to = array($this->to);

		if (!$this->from || !array_notnull($this->to))
		{
			$msg = (!$this->from) ? "보내는 번호가 존재하지 않습니다" : "받는 번호가 존재하지 않습니다";
			$this->error($msg); return false;
		}

		if (!trim(isset($msg)))
		{
			$this->error("메세지를 입력해주세요"); return false;
		}

		$this->call = count($this->to) * $this->multiple;

		$this->getLimit();
		if ($this->call > $this->limit)
		{
			$this->error("보유 SMS 잔여량은 ".number_format($this->limit)."건으로 발송예정건수 ".number_format($this->call)."건보다 부족합니다");
			return;
		}

		unset($data); unset($params);
		$phone_list = array();

		foreach ($this->to as $k=>$v)
		{
			$phone = is_array($v) ? str_replace("-","",$v["phone"]) : str_replace("-","",$v);

			if($phone)
			{
				$phone_list[$k]	= $phone;
			}
		}

		###
		$auth = config_load('master');
		$confirmnum	= (!$this->type) ? md5($auth['sms_auth']) : "";
		
		if (is_array($phone_list) === true)
		{
			foreach ($msg as $_msg)
			{
				//$msgs[]	= @iconv('euc-kr', 'utf-8', $_msg);
				//$this->log($_msg, $v["phone"], $v["mid"]);
				$msgs[] = trim($_msg);
				$str = trim($_msg);
				$euckr_str = mb_convert_encoding($str,'EUC-KR','UTF-8');
				$len = strlen($euckr_str);

			}

			$params[]				= "mallid=" . $CI->config_system['service']['cid'];
			$params[]				= "sms_id=" . $CI->config_system['service']['sms_id'];
			$params[]				= "confirmnum=" . $confirmnum;
			$data['params'		]	= makeEncriptParam(implode("&", $params));
			$data['mode'		]	= 'sms_send';
			$data['type'		]	= $this->type;
			$data['msg'			]	= $msgs;
			$data['from'		]	= $this->from;
			$data['phone_list'	]	= $phone_list;

			if ($this->client->post($this->gabiaUrlPath, $data))
			{
				
				$client_msg = $this->client->getContent();
				
				if ($client_msg == 'ok')
				{
					if($len >90){
						$this->msg	= $this->call . "개의 LMS를 발송하였습니다";
					}else{
						$this->msg	= $this->call . "개의 SMS를 발송하였습니다";
					}
					
					$this->limit-= $this->call;
					return true;
				}else if($client_msg=="not5") {
						$this->msg	= "인증번호가 올바르지않습니다 다시 확인해주세요.";
					return false;
				}else if($client_msg=="not1") {
						$this->msg	= "잘못된 접근입니다.";
					return false;
				}else if($client_msg=="not2") {
						$this->msg	= "SMS 정보가 존재 하지 않습니다.";
					return false;
				}else if($client_msg=="not3") {
						$this->msg	= "등록된 서버가 아닙니다.";
					return false;
				}else if($client_msg=="not3") {
						$this->msg	= "등록된 서버가 아닙니다.";
					return false;
				} else {
					$this->msg	= "발송되었습니다. \n SMS 발송내역에서 상세내용을 확인해주세요.";
					return false;
				}
			}
		}
		$this->msg	= "발송이 되지 않았습니다.";
		return false;
	}

	public function sendmsg($from,$to,$msg)
	{
		$totallen = strlen($this->sms_account) + strlen($this->sms_password) + strlen($to) + strlen($from) + strlen($this->smsStatus) + strlen($this->cTrandate) + strlen($msg);

		$parameters = $this->sms_key.",";
		$parameters .= $totallen.",";
		$parameters .= $this->sms_account.",";
		$parameters .= $this->sms_password.",";
		$parameters .= $to.",";
		$parameters .= $from.",";
		$parameters .= $this->smsStatus.",";
		$parameters .= $this->cTrandate.",";
		//$parameters .= $this->cTranmsg.$smsEndOfCommand;
		$parameters .= $msg;

		$this->sdpQuery = $parameters.$this->smsEndOfCommand;
		fputs($this->Socket, $this->sdpQuery);
	}

	public function log($msg, $to="", $mid="")
	{
		$msg	= addslashes($msg);
		$query = "
		insert into gs_log_sms set
			from_cellphone	= '$this->from',
			groupkey		= '$this->groupkey',
			to_cellphone	= '$to',
			contents		= '$msg',
			mid				= '$mid',
			regdate			= now()
			";
		$Cl->db->sqlQuery($query);
		$Cl->cno = $this->db->id;
	}

	public function setTypes () { $this->type = true; }

	public function getLimit()
	{	
		/*
		if(!$this->connect()) return ;

		$parameters = $this->sms_key.",";
		$parameters .= "0,";
		$parameters .= $this->sms_account.",";
		$parameters .= $this->sms_password;

		$this->sdpQuery = $parameters.$this->smsEndOfCommand;
		fputs($this->Socket, $this->sdpQuery);

		$this->limit = $this->read();
		*/
		$data['mode'	]	= 'getSmsCount';
		$data['params'	]	= makeEncriptParam("mallid=" . $this->gsinfo['cid'] . "&sms_id=" . $this->gsinfo['sms_id']);
		if ($this->client->post($this->gabiaUrlPath, $data)){
			$this->limit = $this->client->getContent();
		}else{
			$this->limit = -1;
		}
	}

	public function Read()
	{
		$smsResponse = "";
		$buffer = "";

		while (!feof($this->Socket))
		{
		//echo "test";
			$buffer = fgets($this->Socket, 1024);

			if (strcmp($buffer, $this->smsEndOfCommand) == 0)
			{
				break;
			}
			$smsResponse = $smsResponse.$buffer;

		}
		return($smsResponse);
	}

}
?>