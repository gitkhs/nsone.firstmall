<?php
class firstmall_crypt {	
	
	var $iv = 0;
	
	function firstmall_crypt(){
		$this->iv = substr(decbin('4328105527907610'),0,16);		
	}

	function cut_key($key){
		return substr($key,0,15);
	}

	function pad ($text) {				
		$pad = 16 - (strlen($text) % 16);
		return $text . str_repeat(chr($pad), $pad);
	}
	
	function unpad($text) {
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return false;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
		return substr($text, 0, -1 * $pad);
	}
	
	function encrypt($value){
		$key = $this->cut_key($key);
		$value = $this->pad($value);
		$result = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, "gabiaFirstmall2104", $value, MCRYPT_MODE_CBC, $this->iv );
		$result = base64_encode($result);		
		return $result;		
	}

	function decrypt($value){
		$key = $this->cut_key($key);
		$value = base64_decode($value);	
		$result = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, "gabiaFirstmall2104", $value, MCRYPT_MODE_CBC, $this->iv );		
		$result = $this->unpad($result);		
		return $result;		
	}
}

// END firstmall_crypt Class

/* End of file firstmall_crypt.php */
/* Location: ./app/libraries/firstmall_crypt.php */