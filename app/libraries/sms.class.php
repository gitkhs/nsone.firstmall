<?
//라이브러리

include_once ROOTPATH."/app/libraries/lib/gabia_xmlrpccommon.php";
include_once ROOTPATH."/app/libraries/lib/simple_parser.php";

class gabiaSmsApi extends XmlRpcCommon
{

	var $gabiaUrl			= 'http://firstmall.kr';
	var $gabiaUrlPath		= '/payment_firstmall/get_apikey.php';
	var $api_host = "sms.firstmall.kr";
	var $api_curl_url = "http://sms.firstmall.kr/assets/api_upload.php";
	var $user_id = "";
	var $user_pw = "";
	var $m_szResultXML = "";
	var $m_oResultDom = null;
	var $m_szResultCode = "";
	var $m_szResultMessage = "";
	var $m_szResult = "";
	var $sms_reserve = 0;

	var $m_nBefore = 0;
	var $m_nAfter = 0;

	var $md5_access_token = "";

	var $RESULT_OK = "0000";
	var $CALL_ERROR = -1;

	var $sms_id;
	var $api_key;
	var $sms_pw;

	//function __construct($id, $api_key, $pw="")
	function gabiaSmsApi($id, $api_key, $pw="")
	{
		$CI =& get_instance();

		$sms_info = config_load('master');

		//기존 방식인 경우 신규 방식으로 변환위해 api_key 셋팅 및 업데이트
		if($sms_info["sms_auth"] != "" && strlen($sms_info["sms_auth"]) != 32){

			$data = makeEncriptParam("mallid=" . $CI->config_system['service']['cid'] . "&sms_id=" . $CI->config_system['service']['sms_id']);

			$res = file_get_contents($this->gabiaUrl.$this->gabiaUrlPath."?params=".$data);

			if ($res){
				$api_key = $res;

				$query = "
				update fm_config set
					value	= '".$api_key."'
					where	codecd = 'sms_auth'
					";

				$CI->db->query($query);

			}
		}

		$this->sms_id = $id;
		$this->api_key = $api_key;
		$this->sms_pw = $pw;

		$nonce = $this->gen_nonce();
		$this->md5_access_token = $nonce.md5($nonce.$this->api_key);

	}

	function __destruct()
	{
		unset($this->m_szResultXML);
		unset($this->m_oResultDom);
	}

	/*
	 * nonce 생성
	 */
	function gen_nonce()
	{
		$nonce = '';
		for($i=0; $i<8; $i++)
		{
			$nonce .= dechex(rand(0, 15));
		}

		return $nonce;
	}

	function getSmsCount()
	{
		$request_xml = sprintf("<request>
<sms-id>%s</sms-id>
<access-token>%s</access-token>
<response-format>xml</response-format>
<method>SMS.getUserInfo</method>
<params>
</params>
</request>", $this->sms_id, $this->md5_access_token );

		$nCount = 0;

		if ($this->xml_do($request_xml) == $this->RESULT_OK)
		{
			//echo $this->m_szResult;

			if (strpos($this->m_szResult, "<?xml") == 0)
			{
				/*
				$oCountXML = simplexml_load_string($this->m_szResult);

				//print_r($oCountXML);
				$child = $oCountXML->children();
				*/

				include_once dirname(__FILE__)."/SofeeXmlParser.php";
				$xmlParser = new SofeeXmlParser();
				$xmlParser->parseString($this->m_szResult);
				$tree = $xmlParser->getTree();

				$child = null;
				foreach((array)$tree['root'] as $k=>$v) $child->$k = $tree['root'][$k]['value'];

				if ( isset($child->sms_quantity) )
					$nCount = $child->sms_quantity;

			}

		}

		return $nCount;
	}

	function get_result_xml($result)
	{
		$sp = new SimpleParser();
		$sp->parse_xml($result);

		$result_xml = $sp->getValue("RESPONSE|RESULT");

		return base64_decode($result_xml);
	}

	function get_status_by_ref($refkey)
	{
		if(is_array($refkey))
		{
			$ref_keys = implode(",", $refkey);
		}else
		{
			$ref_keys = $refkey;
		}
		$request_xml = <<<DOC_XML
<request>
<sms-id>{$this->sms_id}</sms-id>
<access-token>{$this->md5_access_token}</access-token>
<response-format>xml</response-format>
<method>SMS.getStatusByRef</method>
<params>
	<ref_key>{$ref_keys}</ref_key>
</params>
</request>
DOC_XML;

		if ($this->xml_do($request_xml) == $this->RESULT_OK)
		{
			$r = array();
			$resultXML = simplexml_load_string($this->m_szResult);

			$child = $resultXML>children();

			foreach($child->smsResult->entries->children() as $n)
			{
				$child2 = $n->children();
				$szKey = (string)$child2->SMS_REFKEY;
				$szCode = (string)$child2->CODE;
				$szMesg = (string)$child2->MESG;

				if (array_key_exists($szKey, $r))
				{
					$r[$szKey]["CODE"] = $szCode;
					$r[$szKey]["MESG"] = $szMesg;
				}
				else
					$r[$szKey] = array("CODE" => $szCode, "MESG" => $szMesg);
			}

			return $r;
		}
		else false;
	}

	function sms_msg_type($msg){

		$patterns[0] = "&";
		$patterns[1] = "<";
		$patterns[2] = ">";
		$replacements[0] = "&amp;";
		$replacements[1] = "&lt;";
		$replacements[2] = "&gt;";

		$msg		= str_replace($replacements, $patterns, $msg);
		
		$euc_kr_msg = iconv('utf-8', 'euc-kr', $msg);
		if(strlen($euc_kr_msg) > 90){
			$sendType	= "lms";
		}else{
			$sendType	= "sms";
		}

		return $sendType;
	}

	function sms_send($phone, $callback, $msg, $refkey="", $reserve = "0", $ordno="", $sendType=''){

		$title = '';
		$patterns[0] = "/&/";
		$patterns[1] = "/</";
		$patterns[2] = "/>/";
		$replacements[2] = "&amp;";
		$replacements[1] = "&lt;";
		$replacements[0] = "&gt;";

		$title = "";
		if(is_array($msg)){
			foreach($msg as $key=>$value){
				$msg[$key]		= preg_replace($patterns, $replacements, $msg[$key]);
			}
			$checkmsg	= $msg[0];
			if(!$sendType) $sendType = $this->sms_msg_type($msg[0]);
			if($sendType == "lms") $title = mb_substr($msg[0], 0, 20);
			
			$msg = join("|^|", $msg);
		}else{
			$msg		= preg_replace($patterns, $replacements, $msg);
			$checkmsg	= $msg;
			if(!$sendType) $sendType = $this->sms_msg_type($msg);
			if($sendType == "lms") $title = mb_substr($msg, 0, 20);

		}

		$boolen = false;
		$pos = substr(strstr($checkmsg, "%"), 1);

		while($pos){
			if(substr($pos, 0, 1) != " "){
				$boolen = true;
			}
			$pos = substr(strstr($pos, "%"), 1);
		}
		
		if($boolen){
			return "fail";
		}


		if(is_array($phone)){
			$phone = join(",", $phone);
		}



		$request_xml = <<<DOC_XML
<request>
<sms-id>{$this->sms_id}</sms-id>
<access-token>{$this->md5_access_token}</access-token>
<response-format>xml</response-format>
<method>SMS.send4</method>
<params>
	<send_type>{$sendType}</send_type>
	<ref_key>{$refkey}</ref_key>
	<subject>{$title}</subject>
	<message>{$msg}</message>
	<callback>{$callback}</callback>
	<phone>{$phone}</phone>
	<reserve>{$reserve}</reserve>
	<ordno>{$ordno}</ordno>
	<deliveryno>{$deliveryno}</deliveryno>
	<deliveryco>{$deliveryco}</deliveryco>
</params>
</request>
DOC_XML;
		//echo "<xmp>".$request_xml."</xmp><br><br><br><br><br>";
		
		return $this->xml_do($request_xml);
	}

	function lms_send($phone, $callback, $msg, $title="", $refkey="", $reserve = "0")
	{
		$patterns[0] = "/&/";
		$patterns[1] = "/</";
		$patterns[2] = "/>/";
		$replacements[2] = "&amp;";
		$replacements[1] = "&lt;";
		$replacements[0] = "&gt;";

		$msg = preg_replace($patterns, $replacements, $msg);
		$request_xml = <<<DOC_XML
<request>
<sms-id>{$this->sms_id}</sms-id>
<access-token>{$this->md5_access_token}</access-token>
<response-format>xml</response-format>
<method>SMS.send</method>
<params>
		<send_type>lms</send_type>
		<ref_key>{$refkey}</ref_key>
		<subject>{$title}</subject>
		<message>{$msg}</message>
		<callback>{$callback}</callback>
		<phone>{$phone}</phone>
		<reserve>{$reserve}</reserve>
</params>
</request>
DOC_XML;

		return $this->xml_do($request_xml);
	}

	/*
	 * XMLRPC 발송
	 * $xml_data : 발송정보의 XML 데이터
	 */
	function xml_do($xml_data)
	{
		$this->init($this->api_host, "api", "gabiasms");
		$this->m_szResultXML = $this->call($xml_data);

		if ($this->m_szResultXML)
		{
			/*
			$this->m_oResultDom = simplexml_load_string($this->m_szResultXML);

			$child = $this->m_oResultDom->children();
			*/

			include_once dirname(__FILE__)."/SofeeXmlParser.php";
			$xmlParser = new SofeeXmlParser();
			$xmlParser->parseString($this->m_szResultXML);
			$tree = $xmlParser->getTree();
			$child = null;
			foreach((array)$tree['response'] as $k=>$v) $child->$k = $tree['response'][$k]['value'];

			if (isset($child->code))
			{
				$this->m_szResultCode = $child->code;
				$this->m_szResultMessage = $child->mesg;
			}

			if (isset($child->result))
				$this->m_szResult = base64_decode($child->result);

			//$r = stripos($this->m_szResult, "<?xml");
			$this->m_szResult = strtolower($this->m_szResult);
			$r = strpos($this->m_szResult, "<?xml");
			if ($r == 0 && $r !== FALSE)
			{
				/*
				$oCountXML = simplexml_load_string($this->m_szResult);
				$child = $oCountXML->children();
				*/
				$xmlParser->parseString($this->m_szResultXML);
				$tree = $xmlParser->getTree();
				$child = null;
				foreach((array)$tree['response'] as $k=>$v) $child->$k = $tree['response'][$k]['value'];

				if (isset($child->BEFORE_SMS_QTY))
					$this->m_nBefore = $child->BEFORE_SMS_QTY;

				if (isset($child->AFTER_SMS_QTY))
					$this->m_nAfter = $child->AFTER_SMS_QTY;

				unset($oCountXML);
			}

			unset($this->m_oResultDom);


		}
		else
		{
			$this->m_szResultCode = $this->m_szResultXML;
			$this->m_szResult = $this->getRpcError();
		}

		return $this->m_szResultCode;
	}

	function getResultCode()
	{
		return $this->m_szResultCode;
	}

	function getResultMessage()
	{
		return $this->m_szResultMessage;
	}

	function getBefore()
	{
		return $this->m_nBefore;
	}

	function getAfter()
	{
		return $this->m_nAfter;
	}

	function minusSmsCount($smstype, $msg="고객리마인드 서비스 - 추가차감")
	{
		$request_xml = sprintf("<request>
<sms-id>%s</sms-id>
<access-token>%s</access-token>
<response-format>xml</response-format>
<method>SMS.minusSmscount</method>
<params>
	<smstype>%s</smstype>
	<message>%s</message>
</params>
</request>", $this->sms_id, $this->md5_access_token, $smstype, $msg );
		return $this->xml_do($request_xml);
	}


	//SMS 메시지 치환 등
	function sendSMS($commonSmsData){
		
		$CI =& get_instance();
		$CI->config_basic = ($CI->config_basic)?$CI->config_basic:config_load('basic');
		$CI->config_basic['domain'] = $CI->config_system['domain'];
		$from_sms	= $CI->config_sms_info['send_num'] ? $CI->config_sms_info['send_num'] : $CI->config_basic['companyPhone'];
		$from_sms	= ereg_replace("[^0-9]", "", $from_sms);

		$keys = array_keys($commonSmsData);

		
		foreach($keys as $case){

			$msg = array();
			$phone = array();
		
			## 개인맞춤형알림(예약 발송) 추가로 인한 SMS 구분 2014-07-21
			$case_tmp = explode("_",$case);
			if($case_tmp[0] == "personal"){
				$sms_mode = "sms_personal";
			}else{
				$sms_mode = "sms";
			}

			$CI->config_sms_info = ($CI->config_sms_info)?$CI->config_sms_info:config_load('sms_info');
			$CI->config_sms		= ($CI->config_sms['groupcd'] == $sms_mode )?$CI->config_sms:config_load($sms_mode);

			$limit	= 1;

			$to_sms		= $commonSmsData[$case]['phone'];
			$params		= $commonSmsData[$case]['params'];
			$order_no	= $commonSmsData[$case]['order_no'];
			$mid		= $commonSmsData[$case]['mid'];

			if($limit>0) {
				switch($case){
					case 'member'://회원 관련 SMS 발송(관리자 없음)				
						if($params['msg']){
							$result = $this->sendSMS_Msg($params['msg'], $to_sms);
						}
						break;
					case 'restock'://재입고 알림 SMS 발송(관리자 없음)
						foreach($params['msg'] as $key=>$message){
							$result = $this->sendSMS_Msg($params['msg'][$key], $to_sms[$key]);
						}
						break;
					case 'goods_review'://상품후기 적립금지급시(관리자없음)
						if($params['msg']){
							$result = $this->sendSMS_Msg($params['msg'], $to_sms);
						}
						break;
					case 'board_reply'://게시판답변용(관리자없음)
						$makeMsg = sendCheck('cs', 'sms', 'user', $params, false,$CI->config_sms);
						if($makeMsg){
							## 발송시간제한
							$this->sms_reserve = $this->sendSMS_restriction($case);
							$result = $this->sendSMS_Msg($makeMsg, $to_sms);
						}
						break;
					default://기본
						### USER
						$senduse		= array();
						$remind_param	= array();
						if(is_array($to_sms)){

							$to_sms_count = count($to_sms);
							$before_ordno='';
							$before_delivery_number='';

							for($i=0; $i<$to_sms_count; $i++){
								$makeMsg = sendCheck($case, $sms_mode, 'user', $params[$i], $order_no[$i],$CI->config_sms);
								
								//메시지가 없거나 중복된 메시지일 경우 제거 하여 빈값 발송 및 중복 발송 방지
								if(trim($makeMsg) && (($params[$i]['ordno'] != $before_ordno || $params[$i]['delivery_number'] != $before_delivery_number)|| $params[$i]['ordno'] == '')){
									## 고객리마인드
									if($sms_mode == "sms_personal"){
										$msg[]						= $makeMsg[0];
										$remind_param_tmp			= array();
										$remind_param_tmp['data']	= $params[$i];
										$remind_param_tmp['phone']	= $to_sms[$i];
										$remind_param[]				= $remind_param_tmp;
									}else{
									## 일반 sms
										$msg[]		= $makeMsg;
									}
									$senduse[] = true;
									$phone[] = $to_sms[$i];

								}else{
									$senduse[] = false;
								}

								$before_ordno			= $params[$i]['ordno'];
								$before_delivery_number = $params[$i]['delivery_number'];
							}
						}
						
						## 발송시간제한(예약문자)
						$rest_use = 'y';
						if($case == 'order' || $case == 'settle') if($order_user != 'admin') $rest_use = 'n';
						if($sms_mode == "sms_personal") $rest_use = 'n';
						## 고객리마인드 예약시간은 /app/helpers/reservation_helper.php 에서 설정됨.
						if($rest_use == 'y') $CI->sms_reserve = $this->sendSMS_restriction($case);

						if($msg){
							$result = $this->sendSMS_Msg($msg, $phone, $order_no,$sms_mode);
							### 고객리마인드서비스용 발송 로그저장 ## 발송 LOG
							if($sms_mode == "sms_personal"){
								## 발송여부,발송결과,치환코드,발송내용
								$this->remind_log($senduse,$result,$remind_param,$msg);
							}
						}
						

						### ADMIN
						if($CI->config_sms_info['admis_cnt']>0){
							$dataTo = array();
							unset($msg);
							
							if(is_array($to_sms)){
								$msg = array();

								$before_ordno='';
								$before_delivery_number='';

								for($i=0; $i<$to_sms_count; $i++){
									$makeMsg = sendCheck($case, 'sms', 'admin', $params[$i], $order_no[$i],$CI->config_sms);
									
									//메시지가 없거나 중복된 메시지일 경우 제거 하여 빈값 발송 및 중복 발송 방지
									if(trim($makeMsg) && (($params[$i]['ordno'] != $before_ordno || $params[$i]['delivery_number'] != $before_delivery_number) || $params[$i]['ordno'] == '')){
										
										for($j=0;$j<$CI->config_sms_info['admis_cnt'];$j++){
											
											if(adminSendChK($case, $j)=='Y'){
												$id			= "admins_num_".$j;
												$msg[]	= $makeMsg;
												$dataTo[]		= ereg_replace("[^0-9]", "", $CI->config_sms_info[$id]);											
											}
										}

									}
									$before_ordno = $params[$i]['ordno'];
									$before_delivery_number = $params[$i]['delivery_number'];
								}
								
								$adminResult		= $this->sendSMS_Msg($msg, $dataTo, $order_no);
							}
							
						}
						break;
				}
			}
		}
		
		return $result;
	}


	#발송시간제한 : 발송예약시간 설정
	function sendSMS_restriction($case){

		$CI		=& get_instance();
		$CI->config_sms_rest	= config_load('sms_restriction');	//SMS 발송시간제한

		if(strstr($case,"_write") || strstr($case,"_reply")){
		## 게시판 발송시간 제한(예약)
			if(strstr($case,"_write")){
				$sms_use_chk	= $CI->config_sms_rest['board_toadmin'];
			}else{
				$sms_use_chk	= $CI->config_sms_rest['board_touser'];
			}
			$config_time_s	= $CI->config_sms_rest['board_time_s'];
			$config_time_e	= $CI->config_sms_rest['board_time_e'];
			$reserve_time	= $CI->config_sms_rest['board_reserve_time'];

		}else{
		## 일반 발송시간 제한(예약)
			$config_time_s	= $CI->config_sms_rest['config_time_s'];
			$config_time_e	= $CI->config_sms_rest['config_time_e'];
			$reserve_time	= $CI->config_sms_rest['reserve_time'];
			$sms_use_chk	= $CI->config_sms_rest[$case];

		}
		if($sms_use_chk == "checked"){

			//발송제한 시작 시간이 더 크면, 발송제한 종료시간은 익일로 계산.
			$rest_stime	= date("Y-m-d ".$config_time_s.":00:00",mktime());
			if($config_time_s > $config_time_e){
				$rest_etime	= date("Y-m-d ".$config_time_e.":59:59",mktime()+(60*60*24));
			}else{
				$rest_etime	= date("Y-m-d ".$config_time_e.":59:59",mktime());
			}
			//SMS발송시각이 발송제한 시간에 해당하면 지정된 예약시간에 발송
			if($rest_stime <= date("Y-m-d H:i:s",mktime()) && $rest_etime >= date("Y-m-d H:i:s",mktime())){
				$rest_etime_tmp = date("Y-m-d 08:00:00",strtotime($rest_stime)+(60*60*24));	//익일 08시
				$rest_etime_tmp = strtotime($rest_etime_tmp) + (60*$reserve_time); //익일 08시+예약time
				$this->sms_reserve = date("Y-m-d H:i:s",$rest_etime_tmp);
			}
		}
		return $this->sms_reserve;
	}


	// 자동 SMS 발송용
	function sendSMS_Msg($msg, $dataTo, $order_no="",$sms_mode=''){
		$CI		=& get_instance();
		$CI->config_basic = ($CI->config_basic)?$CI->config_basic:config_load('basic');
		$CI->config_sms_info = ($CI->config_sms_info)?$CI->config_sms_info:config_load('sms_info');

		// 회원리스트 > sms : 발송 보내는 사람
		if (!empty($_POST["send_sms"])) {
			$from_sms = ereg_replace("[^0-9]", "", $_POST["send_sms"]);
		} else {
			$from_sms	= $CI->config_sms_info['send_num'] ? $CI->config_sms_info['send_num'] : $CI->config_basic['companyPhone'];
		}

		if($sms_mode == "sms_personal"){
			$sms_msg_type = $this->sms_msg_type($msg[0]);
			if($sms_msg_type == "sms"){
				 $addres	= $this->sms_test('cm2',"고객리마인드 추가차감-".$msg[0]);
				 $sms_type	= "sms";
			}
		}
		$CI->benchmark->mark('code_start'); 
		//발송 데이터가 많을 경우 분할 발송하여 시간 단축 (1만건 발송시 약 3분 소요)
		//echo "SMS Send = >";
		if(count($dataTo) > 2000){
			$j=0;
			for($i=0; $i<count($dataTo); $i++){
				$new_dataTo[$j] = $dataTo[$i];
				if(is_array($msg)){
					$new_msg[$j] = $msg[$i];
				}else{
					$new_msg[$j] = $msg;
				}
				$new_ord_no[$j] = $order_no[$i];
				$j++;
				## 2000건씩 발송.
				if(($i+1) % 2000 == 0 || (count($dataTo)-1) == $i){
					$reserve		= !empty($this->sms_reserve) ? $this->sms_reserve : 0;
					$result			= $this->sms_send($new_dataTo, $from_sms, $new_msg, "", $reserve, $new_ord_no,$sms_type);
					$result_code	= $this->getResultCode();
					## 수신대상, 수신메세지 등 초기화
					$new_dataTo		= array();
					$new_msg		= array();
					$new_ord_no		= array();
					$j=0;
				}
			}
		}else{
			$reserve	= !empty($CI->sms_reserve) ? $CI->sms_reserve : 0;
			$result		= $this->sms_send($dataTo, $from_sms, $msg, "", $reserve, $order_no,$sms_type);
			$result_code = $this->getResultCode();
		}
		$CI->benchmark->mark('code_end'); 
		$sms_send_times = $CI->benchmark->elapsed_time('code_start', 'code_end'); 
		//echo $sms_send_times;
		return $result;
	}

	// SMS 발송건수 차감
	function sms_test($count=1,$msg){

		$CI		=& get_instance();
		$limit		= $this->minusSmsCount($count,$msg);

		return $limit;
	}


	#고객리마인드서비스용 발송 로그저장 ## 발송 LOG
	function remind_log($arr_senduse,$result,$params, $msg){
		
		$CI =& get_instance();

		foreach($arr_senduse as $k=>$senduse){

			if($senduse){
				## 발송통계
				$sql = "select seq from fm_log_curation_summary where inflow_kind='".$params[$k]['data']['kind']."' and send_date ='".date("Y-m-d",mktime())."'";
				$query	= $CI->db->query($sql);
				$res	= $query->row_array();
				if(!$res['seq']){
					$CI->db->query("insert into fm_log_curation_summary set inflow_kind='".$params[$k]['data']['kind']."',send_sms_total=0,send_date ='".date("Y-m-d",mktime())."'");
					$summary_seq = $CI->db->insert_id();
				}else{
					$summary_seq = $res['seq'];
				}
			}else{ $summary_seq = 0; }

			$memo = "";
			if(!$senduse){ $memo = "ERROR : MSG 누락@@";}
			if(!$result){ $memo = "ERROR : 전송실패!@@"; }
			if($memo){ $memo .= serialize($CI->config_sms)."@@".serialize($msg[$k]); }

			unset($log_params);
			$log_params['regist_date']	= date('Y-m-d H:i:s');
			$log_params['sms_cnt']		= '3';
			$log_params['summary_seq']	= $summary_seq;
			$log_params['sendres']		= ($senduse)? 'y':'n';				//제목없으면 false, 발송안함.
			$log_params['kind']			= $params[$k]['data']['kind'];
			$log_params['to_mobile']	= $params[$k]['phone'];
			$log_params['member_seq']	= $params[$k]['data']['member_seq'];
			$log_params['sms_msg']		= $msg[$k];
			$log_params['memo']			= $memo;
			$log_params['reserve_date']	= $CI->sms_reserve;

			$logdata = filter_keys($log_params, $CI->db->list_fields('fm_log_curation_sms'));
			$log_result =  $CI->db->insert('fm_log_curation_sms', $logdata);
			### 일자별 발송 통계(정상 발송 되었을 경우에만 저장)
			if($log_result && $senduse){
				if($summary_seq){
					$CI->db->query("update fm_log_curation_summary set send_sms_total=send_sms_total+1 where seq='".$summary_seq."'");
				}else{
					$CI->db->query("insert into fm_log_curation_summary set inflow_kind='".$params[$k]['data']['kind']."',send_sms_total=1,send_date ='".date("Y-m-d",mktime())."'");
				}
			}
		}
	}

}

?>
