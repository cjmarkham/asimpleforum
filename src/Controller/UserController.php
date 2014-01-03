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

		$comments = $this->app['user']->find_comments($user['id'], 1);

		return $this->app['twig']->render('User/index.twig', array(
			'title' 			=> $user['username'],
			'section'			=> 'members',
			'profile'			=> $user,
			'comments'			=> $comments['data']['data']
		));
	}
}