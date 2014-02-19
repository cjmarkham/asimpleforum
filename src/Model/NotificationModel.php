<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;

class NotificationModel 
{
	/**
	 * Silex App
	 * @var object
	 */
	public $app;

	/**
	 * Set the silex app object
	 * @param SilexApplication $app
	 */
	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function add (Request $request)
	{
		$user_id = (int) $request->get('user_id');
		if (!$user_id)
		{
			return false;
		}

		$notification = $request->get('notification');

		$this->app['db']->insert('notifications', [
			'user_id' => $user_id,
			'notification' => $notification,
			'added' => time()
		]);

		return true;
	}

	public function findByUser ($user_id)
	{
		$user_id = (int) $user_id;
		if (!$user_id)
		{
			return false;
		}

		return $this->app['db']->fetchAll('SELECT * FROM notifications WHERE user_id=?', [
			$user_id
		]);
	}
}