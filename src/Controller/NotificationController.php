<?php

namespace Controller;


class NotificationController extends Controller
{
	public $login_required = true;

	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{ 
		$user = $this->app['session']->get('user');
		if (!$user)
		{
			return $this->app->redirect('/');
		}

		return $this->app['twig']->render('Notifications/index.twig', array(
			'title' 			=> 'Notifications',
			'section'			=> 'user',
			'user'				=> $user
		) + $this->extras);
	}
}