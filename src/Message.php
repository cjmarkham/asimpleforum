<?php

use Silex\Application;

class Message
{
	public static $app;

	public static function error($message, $session = true)
	{
		return self::alert($message, true, $session);
	}

	public static function alert($message, $error = false, $session = true)
	{	
		$type = 'info';
		
		if ($error)
		{
			$type = 'danger';
		}

		$message = self::$app['language']->phrase($message);

		if ($session === true)
		{
			self::$app['session']->getFlashBag()->set('alert', array('message' => $message, 'error' => $error, 'type' => $type));
		}
		
		return json_encode(array('message' => $message, 'error' => $error));
	}
}