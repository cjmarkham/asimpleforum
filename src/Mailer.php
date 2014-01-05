<?php

class Mailer
{
	private static $template = null;
	private static $params = array();

	public static function setTemplate ($template, $params = array())
	{
		self::$template = file_get_contents(dirname(__DIR__) . '/public/emails/' . $template . '.html');
		self::$params = $params;
	}

	public static function send ($to, $from, $subject)
	{
		self::compile();

		var_dump($to);

		$headers = "From: " . $from . "\r\n";
		$headers .= "Reply-To: ". $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		try
		{
			$send = mail('doddsey65@hotmail.com', 'doddsey65@hotmail.com', self::$template, $headers);
			var_dump($send);
		}
		catch (\Exception $e)
		{
			die($e->getMessage());
		}
	}

	private static function compile ()
	{
		$params = self::$params;

		foreach ($params as $key => $param)
		{
			self::$template = str_replace('{{ ' . $key . ' }}', $param, self::$template);
		}
	}
}