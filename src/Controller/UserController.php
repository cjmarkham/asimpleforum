<?php

namespace Controller;

class UserController
{
	public $app;

	public function index($username)
	{
		$user = $this->app['user']->find_by_username($username);

		if (!$user)
		{
			return $this->app->redirect('/');
		}

		return $this->app['twig']->render('User/index.twig', array(
			'title' 			=> $user['username'],
			'section'			=> 'members'
		) + $this->params);
	}
}