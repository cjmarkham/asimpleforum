<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function signup($request = array())
	{ 
		if ($request)
		{
			$data = array(
				'username' => $request->get('username'),
				'password' => $request->get('password'),
				'confirm'  => $request->get('confirm'),
				'email'    => $request->get('email'),
				'terms'    => $request->get('terms')
			);

			$signup_attempt = $this->app['auth']->signup($data);

			if ($signup_attempt === true)
			{
				$this->app->redirect('/');
			}
		}

		return $this->app['twig']->render('Auth/signup.twig', array(
			'title' 			=> 'Sign up',
			'section'			=> 'members',
			'data'				=> isset($data) ? $data : null
		) + $this->extras);
	}

	public function logout ()
	{
		$this->app['session']->remove('userId');
		$this->app['session']->remove('user');

		return true;
	}
}