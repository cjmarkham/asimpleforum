<?php

class Logger
{
	public static $error_file = 'error.log';

	public static function error ($title, $content)
	{
		$handle = fopen(dirname(__DIR__) . '/logs/' . self::$error_file, 'a');
		fwrite($handle, str_repeat('=', 30) . ' ' . date('d-m-y h:i a') . ' ' . str_repeat('=', 30) . "\n" . $title . "\n" . $content . "\n\n");
		fclose($handle);

		return;
	}
}