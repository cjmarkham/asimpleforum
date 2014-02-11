<?php

namespace ASF;

/**
 * A wrapper class for sending emails
 */
class Mailer
{
	/**
	 * The template html
	 * @var string
	 */
	public static $template = null;

	/**
	 * Key value replacements for template html
	 * @var array
	 */
	private static $params = array();

	/**
	 * Sets the template
	 * @param string $template
	 * @param array  $params An array of parameters to replace in template html
	 */
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

	/**
	 * Sends the compiled email
	 * @param  string $to      
	 * @param  string $from    
	 * @param  string $subject 
	 * @return boolean          
	 */
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

	/**
	 * Compiles the template
	 * @return void
	 */
	public static function compile ()
	{
		$params = self::$params;

		foreach ($params as $key => $param)
		{
			self::$template = str_replace('{{ ' . $key . ' }}', $param, self::$template);
		}
	}
}