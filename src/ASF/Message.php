<?php

namespace ASF;

/**
 * A class for sending messages
 * @todo Intergrate Silex\Response to have one class sending response messages
 */
class Message
{
	/**
	 * Extends alert method
	 * @param  string  $message
	 * @param  boolean $session Should this message be saved as a session
	 * @return self::alert
	 */
	public static function error($message, $session = true)
	{
		return self::alert($message, true, $session);
	}

	/**
	 * Sends an alert to the client
	 * @param  string  $message 
	 * @param  boolean $error   
	 * @param  boolean $session Should this message be saved as a session
	 * @return array
	 */
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