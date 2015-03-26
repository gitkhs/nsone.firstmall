<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function krencode($text)
{
   // global $base, $tmin, $tmax, $skew, $damp, $initial_bias, $initial_n, $prefix, $delim;

	$prefix = "xn--";
	$delim = "-";
	$base = 36;
	$tmin = 1;
	$tmax = 26;
	$skew = 38;
	$damp = 700;
	$initial_bias = 72;
	$initial_n = 128;


    $text = utf8_to_unicode($text);

    $codecount = 0;
    $basic_string = "";
    $extended_string = "";

    for ($i = 0; $i < sizeof($text); $i++)
    {
        if($text[$i] < $initial_n)
    {
            $basic_string .= chr($text[$i]);
            $codecount++;
        }
    }

    $n = $initial_n;
    $delta = 0;
    $bias = $initial_bias;
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
        for($k = $base;1;$k = $k + $base)
        {
            $t = 0;
            if($k <= $bias + $tmin)
            $t = $tmin;
            elseif($k >= $bias + $tmax)
            $t = $tmax;
            else
            $t = $k - $bias;

            if($q < $t)
            break;

            $extended_string .= encode_digit($t + (($q - $t) % ($base - $t)));
            $q = floor(($q - $t) / ($base - $t));
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
    $encoded = $prefix.$basic_string.$delim.$extended_string;
    }
    elseif(strlen($basic_string) < 1 && strlen($extended_string) > 0)
    {
    $encoded = $prefix.$extended_string;
    }

    return($encoded);
}

function adapt($delta, $numpoints, $firsttime)
{

	$prefix = "xn--";
	$delim = "-";
	$base = 36;
	$tmin = 1;
	$tmax = 26;
	$skew = 38;
	$damp = 700;
	$initial_bias = 72;
	$initial_n = 128;


    if($firsttime)
    $delta = floor($delta / $damp);
    else
    $delta = floor($delta / 2);

    $delta = $delta + floor($delta / $numpoints);

    $k = 0;
    while($delta > floor((($base - $tmin) * $tmax) / 2))
    {
    $delta = floor($delta / ($base - $tmin));
    $k = $k + $base;
    }

    return($k + (floor((($base - $tmin + 1) * $delta) / ($delta + $skew))));
}

/*

Function encode_digit and decode_digit were adapted from punycode.c, part of GNU Libidn.

http://www.gnu.org/software/libidn/doxygen/punycode_8c-source.html

*/
function encode_digit($d)
{
    return chr(($d + 22 + 75 * ($d < 26)));
}

function decode_digit($cp)
{

	$prefix = "xn--";
	$delim = "-";
	$base = 36;
	$tmin = 1;
	$tmax = 26;
	$skew = 38;
	$damp = 700;
	$initial_bias = 72;
	$initial_n = 128;


    $cp = ord($cp);
    return ($cp - 48 < 10) ? $cp - 22 : (($cp - 65 < 26) ? $cp - 65 : (($cp - 97 < 26) ? $cp - 97 : $base));
}

/*

Copyright (C) 2002 Scott Reynen

Function utf8_to_unicode and unicode_to_utf8 was taken from an article titled "How to develop multilingual, Unicode
applications with PHP" at the following URL:

http://www.randomchaos.com/document.php?source=php_and_unicode

*/
function unicode_to_utf8( $unicode )
{
    $utf8 = '';

    if ( $unicode < 128 )
    {
        $utf8.= chr( $unicode );
    }
    elseif ( $unicode < 2048 )
    {
        $utf8.= chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
        $utf8.= chr( 128 + ( $unicode % 64 ) );
    }
    else
    {
        $utf8.= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
        $utf8.= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
        $utf8.= chr( 128 + ( $unicode % 64 ) );
    }

    return $utf8;
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
// END
/* End of file krdomain_helper.php */
/* Location: ./app/helpers/krdomain_helper.php */
?>