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
			'added' => date('Y-m-d H:i:s')
		]);

		return true;
	}

	public function findByUser ()
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
			return false;
		}

		$notifications = $this->app['db']->fetchAll('SELECT * FROM notifications WHERE user_id=? ORDER BY added DESC', [
			$user['id']
		]);

		$data = ['unread' => [], 'read' => []];

		if ($notifications) 
		{
			foreach ($notifications as $notification)
			{
				if ($notification['read'])
				{
					$data['read'][] = $notification;
				}
				else
				{
					$data['unread'][] = $notification;
				}
			}
		}

		return json_encode($data);
	}

	public function markRead ()
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
			return false;
		}

		return $this->app['db']->update('notifications', [
			'`read`' => 1
		], ['`read`' => 0, 'user_id' => $user['id']]);
	}
}