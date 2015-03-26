<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Krdomain
{

	var $prefix = "xn--";
	var $delim = "-";
	var $base = 36;
	var $tmin = 1;
	var $tmax = 26;
	var $skew = 38;
	var $damp = 700;
	var $initial_bias = 72;
	var $initial_n = 128;

	function __construct() {
		$this->ci =& get_instance();

	}

	function decode($text)
	{
		//global $base, $tmin, $tmax, $skew, $damp, $initial_bias, $initial_n, $prefix, $delim;

		$n = $this->initial_n;
		$i = 0;
		$bias = $this->initial_bias;
		$output = array();

		if(substr($text, 0, strlen($this->prefix)) != $this->prefix)
		return($text);
		else
		$text = str_replace($this->prefix, "", $text);

		$delim_pos = strrpos($text, $this->delim);

		if($delim_pos !== false)
		{
		for($j = 0; $j < $delim_pos; $j++)
		array_push($output, $text[$j]);
		$text = substr($text, $delim_pos + 1);
		}

		for(; strlen($text) > 0;)
		{
		$oldi = $i;
		$w = 1;

		for($k = $this->base;1; $k = $k + $this->base)
		{
		$digit = decode_digit($text[0]);
		$text = substr($text, 1);
		$i = $i + $digit * $w;

		$t = 0;
		if($k <= $bias + $this->tmin)
		$t = $this->tmin;
		elseif($k >= $bias + $this->tmax)
		$t = $this->tmax;
		else
		$t = $k - $bias;

		if($digit < $t)
		break;

		$w = $w * ($this->base - $t);
		}

		$bias = adapt($i - $oldi, sizeof($output) + 1, $oldi == 0);
		$n = $n + floor($i / (sizeof($output) + 1));
		$i = $i % (sizeof($output) + 1);

		$tmp = $output;
		$output = array();

		$j = 0;
		for($j = 0; $j < $i; $j++)
		array_push($output, $tmp[$j]);
		array_push($output, unicode_to_utf8($n));
		for($j = $j; $j < sizeof($tmp); $j++)
		array_push($output, $tmp[$j]);

		$i++;
		}

		return(implode($output));
	}

	function encode($text)
	{
		//global $base, $tmin, $tmax, $skew, $damp, $initial_bias, $initial_n, $prefix, $delim;

		$text = utf8_to_unicode($text);

		$codecount = 0;
		$basic_string = "";
		$extended_string = "";

		for ($i = 0; $i < sizeof($text); $i++)
		{
		if($text[$i] < $this->initial_n)
		{
		$basic_string .= chr($text[$i]);
		$codecount++;
		}
		}

		$n = $this->initial_n;
		$delta = 0;
		$bias = $this->initial_bias;
		$h = $codecount;

		while($h < sizeof($text))
		{
		$m = 100000;
		for($j = 0; $j < sizeof($text); $j++)
		{
		if($text[$j] >= $n && $text[$j] <= $m)
		{
		$m = $text[$j];
		}
		}

		$delta = $delta + ($m - $n) * ($h + 1);
		$n = $m;

		for($j = 0; $j < sizeof($text); $j++)
		{
		$c = $text[$j];

		if($c < $n)
		$delta++;
		elseif($c == $n)
		{
		$q = $delta;
		for($k = $this->base;1;$k = $k + $this->base)
		{
		$t = 0;
		if($k <= $bias + $this->tmin)
		$t = $this->tmin;
		elseif($k >= $bias + $this->tmax)
		$t = $this->tmax;
		else
		$t = $k - $bias;

		if($q < $t)
		break;

		$extended_string .= encode_digit($t + (($q - $t) % ($this->base - $t)));
		$q = floor(($q - $t) / ($this->base - $t));
		}
		$extended_string .= encode_digit($q);

		$bias = adapt($delta, $h+1, $h==$codecount);
		$delta = 0;
		$h++;
		}
		}
		$delta++;
		$n++;
		}

		if(strlen($basic_string) > 0 && strlen($extended_string) < 1)
		{
		$encoded = $basic_string;
		}
		elseif(strlen($basic_string) > 0 && strlen($extended_string) > 0)
		{
		$encoded = $this->prefix.$basic_string.$this->delim.$extended_string;
		}
		elseif(strlen($basic_string) < 1 && strlen($extended_string) > 0)
		{
		$encoded = $this->prefix.$extended_string;
		}

		return($encoded);
	}

	function adapt($delta, $numpoints, $firsttime)
	{
		//global $base, $tmin, $tmax, $skew, $damp;

		if($firsttime)
		$delta = floor($delta / $this->damp);
		else
		$delta = floor($delta / 2);

		$delta = $delta + floor($delta / $numpoints);

		$k = 0;
		while($delta > floor((($this->base - $this->tmin) * $this->tmax) / 2))
		{
		$delta = floor($delta / ($this->base - $this->tmin));
		$k = $k + $this->base;
		}

		return($k + (floor((($this->base - $this->tmin + 1) * $delta) / ($delta + $this->skew))));
	}

	/*

	Function encode_digit and decode_digit were adapted from punycode.c, part of GNU Libidn.

	http://www.gnu.org/software/libidn/doxygen/punycode_8c-source.html

	*/
	function encode_digit($d)
	{
		return chr(($d + 22 + 75 * ($d < 26)));
	}

	function utf8_to_unicode( $str )
	{

		$unicode = array();
		$values = array();
		$lookingFor = 1;

		for ($i = 0; $i < strlen( $str ); $i++ )
		{

		$thisValue = ord( $str[ $i ] );

		if ( $thisValue < 128 )
		$unicode[] = $thisValue;
		else
		{

		if ( count( $values ) == 0 )
		$lookingFor = ( $thisValue < 224 ) ? 2 : 3;

		$values[] = $thisValue;

		if ( count( $values ) == $lookingFor )
		{
		$number = ( $lookingFor == 3 ) ?
		( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
		 ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );

		$unicode[] = $number;
		$values = array();
		$lookingFor = 1;
		}
		}
		}
		return $unicode;
	}
}
?>