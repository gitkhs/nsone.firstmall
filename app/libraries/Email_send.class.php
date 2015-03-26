<?
class Mail
{
	var $sep = "\n";
	var $sendmail_args = '';
	var $sendmail_path = '/usr/sbin/sendmail';

	public function Mail($params)
	{
		if (isset($params['sendmail_path'])) $this->sendmail_path = $params['sendmail_path'];
        if (isset($params['sendmail_args'])) $this->sendmail_args = $params['sendmail_args'];
	}

	public function error($msg)
	{
		echo "<span style='font:8pt tahoma'><b>[ERROR]</b> $msg</span>";
		return false;
	}

	public function http_src($content)
	{
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		$host = preg_replace("/:[0-9].+$/","",$host); //포트번호 삭제

		$pattern_a = array("@(\s*href|\s*src)(\s*=\s*'{1})(/[^']+)('{1})@ie"
						, "@(\s*href|\s*src)(\s*=\s*\"{1})(/[^\"]+)(\"{1})@ie"
						, "@(\s*href|\s*src)(\s*=\s*)(/[^\s>\"\']+)(\s|>)@ie"
		);
		$replace_a = "'\\1\\2".($host)."\\3\\4'";
		$content = preg_replace($pattern_a, $replace_a, $content);


		return $content;
	}

	public function send($headers, $body)
	{
		$body = stripslashes($body);
		$headerElements = $this->prepareHeaders($headers);

		list($from, $text_headers) = $headerElements;

		if (!isset($from)) return $this->error('No from address given');
		if (!$this->recipients) return $this->error('No to address given');

		$body = $this->setHTMLBody($body, isset($this->bodyFile));

		$body = str_replace('\\','',$this -> http_src($body));

		$body = chunk_split(base64_encode($body));

		$result = 0;

		if (@is_file($this->sendmail_path)) {
			$from = escapeShellCmd($from);
			$return = $from;	// 리턴메일 주소 설정
			$mail = popen($this->sendmail_path . (!empty($this->sendmail_args) ? ' ' . $this->sendmail_args : '') . " -f$return -- $this->recipients", 'w');
			fputs($mail, $text_headers);
			fputs($mail, $this->sep);
			fputs($mail, $body);
			$result = pclose($mail) >> 8 & 0xFF;
		} else {
			$to_email =$headers['To'];
			unset($headers['To']);
			$headerElements = $this->prepareHeaders($headers);
			list($from, $text_headers) = $headerElements;

			$ret = mail($to_email,$headers['Subject'],$body,$text_headers);
			if (!$ret) return $this->error('sendmail [' . $this->sendmail_path . '] is not a valid file');
			//return $this->error('sendmail [' . $this->sendmail_path . '] is not a valid file');
		}

		if ($result != 0) {
			return $this->error('sendmail returned error code ' . $result);
		}

		return true;
	}

	public function encode_2047($subject)
	{
		return '=?utf-8?b?'.base64_encode($subject).'?=';
	}

	public function prepareHeaders($headers)
	{
		$lines	= array();
		$from	= null;

		$headers['MIME-Version'] = '1.0';
		$headers['Content-Type'] = 'text/html;charset=utf-8';
		$headers['Content-Transfer-Encoding'] = 'base64';

		foreach ($headers as $key => $value) {
			if (strcasecmp($key, 'From') === 0){
				$from = $value;
				$value = '"'.$this->encode_2047($headers['Name']).'" <'.$value.'>';
			} else if (strcasecmp($key, 'Subject') === 0) $value = $this->encode_2047($value);
			else if (strcasecmp($key, 'To') === 0) $this->recipients = $value;
			if (strcasecmp($key, 'Name') !== 0) $lines[] = $key . ': ' . $value;
		}
		return array($from, join($this->sep, $lines) . $this->sep);
	}

	public function setHTMLBody($data, $isfile = false)
	{
		if (!$isfile) return $data;
		else return $this->_file2str($isfile);
	}

	public function _file2str($file_name)
	{
		if (!is_readable($file_name)){
			return $this->error('File is not readable ' . $file_name);
		}
		if (!$fd = fopen($file_name, 'rb')) {
			return $this->error('Could not open ' . $file_name);
		}
		$cont = fread($fd, filesize($file_name));
		fclose($fd);
		return $cont;
	}

}
?>