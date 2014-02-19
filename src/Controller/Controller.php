<?php

namespace Controller;

class Controller 
{
	public $app;

	public $extras = array();

	public function init()
	{
		$user_id = $this->app['session']->get('userId');

		if ($user_id !== null)
		{
			$_user = $this->app['user']->findById($user_id);

			$user = $_user['data'];
			$user['settings'] = $_user['data']['settings'];
			$user['profile'] = $_user['data']['profile'];
			$user['group'] = $this->app['group']->findById($user['perm_group']);

			$this->app['session']->set('user', $user);
		}

		$alerts = $this->app['alert']->findByDate(time());

		$this->extras['alerts'] = $alerts;

		$nav_links = $this->app['navLinks'];
		$this->extras['nav_links'] = $nav_links;
	}
}