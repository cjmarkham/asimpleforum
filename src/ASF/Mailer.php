<?php

namespace ASF;

class Mailer
{
	public static $template = null;
	private static $params = array();

	public static function setTemplate ($template, $params = array())
	{
		$file = __DIR__ . '/../View/Emails/' . $template . '.html';

		if (!file_exists($file))
		{
			return false;
		}

		self::$template = file_get_contents($file);
		self::$params = $params;

		return true;
	}

	public static function send ($to, $from, $subject)
	{
		if (self::$template === null) 
		{
			return false;
		}

		self::compile();

		$headers = "From: " . $from . "\r\n";
		$headers .= "Reply-To: ". $from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		try
		{
			mail($to, $from, self::$template, $headers);
		}
		catch (\Exception $e)
		{
			throw new \Exception('Failed to send mail');
		}

		return true;
	}

	public static function compile ()
	{
		$params = self::$params;

		foreach ($params as $key => $param)
		{
			self::$template = str_replace('{{ ' . $key . ' }}', $param, self::$template);
		}
	}
}