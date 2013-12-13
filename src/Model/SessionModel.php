<?php

namespace Model;

class SessionModel
{
	public $app;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function update ()
	{
		$user = $this->app['session']->get('user');

		if (!empty($user))
		{
			$check = $this->app['db']->fetchAssoc('SELECT * FROM sessions WHERE userId=?', array(
				$user['id']
			));
		}
		else
		{
			$check = $this->app['db']->fetchAssoc('SELECT * FROM sessions WHERE ip=? AND userAgent=?', array(
				$this->app['request']->getClientIp(),
				$this->app['request']->headers->get('User-Agent')
			));

			if ($check['userId'])
			{
				$this->app['db']->update('sessions', array(
					'userId' => 0
				), array('id' => $check['id']));
			}
		}

		if (!$check)
		{
			$this->app['db']->insert('sessions', array(
				'userId' => isset($user['id']) ? $user['id'] : 0,
				'ip' => $this->app['request']->getClientIp(),
				'userAgent' => $this->app['request']->headers->get('User-Agent'),
				'active' => time()
			));
		}
		else
		{
			$this->app['db']->update('sessions', array(
				'active' => time()
			), array('id' => $check['id']));
		}
	}

	public function get ()
	{
		$delete = array();
		$visits = array(
			'users' => 0,
			'guests' => 0,
			'online' => array()
		);

		$sessions = $this->app['db']->fetchAll('SELECT s.*, u.username FROM sessions s LEFT JOIN users u ON u.id=s.userId');

		foreach ($sessions as $session)
		{
			if ($session['active'] < time() - 300)
			{
				$delete[] = $session['id'];
			}

			if ($session['userId'] == 0)
			{
				$visits['guests']++;
			}
			else
			{
				$visits['users']++;
				$visits['online'][] = '<a href="/user/' . $session['username'] . '">' . $session['username'] . '</a>';
			}
		}

		if (count($delete) > 0)
		{
			$this->app['db']->executeQuery('DELETE FROM sessions WHERE id IN (' . implode(',', $delete) . ')');
		}

		return $visits;
	}
}