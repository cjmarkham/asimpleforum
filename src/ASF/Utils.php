<?php

namespace ASF;

class Utils
{
	public static function truncate ($string, $length)
	{
		return strlen($string) > $length ? substr($string, 0, $length - 2) . '..' : $string;
	}

	public static function toUrl ($string)
	{
		$string = urlencode($string);
    	$string = str_replace('+', '-', $string);

    	return $string;
	}
}