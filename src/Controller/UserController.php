<?php

namespace Controller;

class UserController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index($username)
	{
		$user = $this->app['user']->find_by_username($username);

		if (!$user)
		{
			return $this->app->redirect('/');
		}

		return $this->app['twig']->render('User/index.twig', array(
			'title' 			=> $user['data']['username'],
			'section'			=> 'members',
			'profileId'			=> $user['data']['id'],
			'profileUser'		=> $user['data'],
			'profile'			=> $user['data']['profile']
		) + $this->extras);
	}
}