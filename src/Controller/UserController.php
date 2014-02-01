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
			return $this->app->redirect('/' . $this->app['board']['base']);
		}

		$loggedInUser = $this->app['session']->get('user');
		$following = false;

		if ($loggedInUser)
		{
			$following = $this->app['user']->check_following($loggedInUser['id'], $user['data']['id']);
		}

		return $this->app['twig']->render('User/index.twig', array(
			'title' 			=> $user['data']['username'],
			'section'			=> 'members',
			'profileId'			=> $user['data']['id'],
			'profileUser'		=> $user['data'],
			'profile'			=> $user['data']['profile'],
			'following'			=> $following
		) + $this->extras);
	}

	public function settings ()
	{
		$user = $this->app['session']->get('user');

		if (!$user)
		{
			return $this->app->redirect('/' . $this->app['board']['base']);
		}

		// reset with profile information
		$user = $this->app['user']->find_by_username($user['username']);

		return $this->app['twig']->render('User/settings.twig', array(
			'title' 			=> 'Settings',
			'section'			=> 'settings',
			'user'				=> $user['data'],
			'profile'			=> $user['data']['profile'],
			'settings'			=> $user['data']['settings']
		) + $this->extras);
	}

	public function confirmEmail ($code)
	{
		list ($email, $user_id) = explode('-', base64_decode($code));

		$confirmed = $this->app['auth']->confirmEmail($email, $user_id);

		return $this->app['twig']->render('User/confirmEmail.twig', array(
			'title' 			=> 'Confirm email',
			'section'			=> 'members',
			'confirmed'			=> $confirmed
		) + $this->extras);
	}
}