<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;

class NotificationModel 
{
	public $app;

	public function add (Request $request)
	{
		$user_id = (int) $request->get('user_id');
		$notification = $request->get('notification');

		$this->app['db']->insert('notifications', [
			'user_id' => $user_id,
			'notification' => $notification,
			'added' => time()
		]);

		return true;
	}
}