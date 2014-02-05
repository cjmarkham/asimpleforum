<?php

namespace ASF;

class Utils
{
	public static function truncate ($string, $length)
	{
		return strlen($string) > $length ? substr($string, 0, $length - 2) . '..' : $string;
	}
}