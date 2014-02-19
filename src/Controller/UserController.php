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
		$profile = $this->app['user']->findByUsername($username);

		if (!$profile['data'])
		{
			return $this->app->redirect('/' . $this->app['board']['base']);
		}

		$loggedInUser = $this->app['session']->get('user');
		$following = false;

		if ($loggedInUser)
		{
			$following = $this->app['user']->checkFollowing($loggedInUser['id'], $profile['data']['id']);
		}

		return $this->app['twig']->render('User/index.twig', array(
			'title' 			=> $profile['data']['username'],
			'section'			=> 'profile',
			'profileId'			=> $profile['data']['id'],
			'profileUser'		=> $profile['data'],
			'profile'			=> $profile['data']['profile'],
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

		$profile = $this->app['user']->findByUsername($user['username']);

		return $this->app['twig']->render('User/settings.twig', array(
			'title' 			=> 'Settings',
			'section'			=> 'settings',
			'user'				=> $user,
			'profile'			=> $profile['data']['profile'],
			'settings'			=> $profile['data']['settings']
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