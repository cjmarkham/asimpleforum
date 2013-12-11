<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class AuthController extends BaseController
{
	public $app;

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
				header ('Location: /');
				exit;
			}
		}

		return $this->app['twig']->render('Auth/signup.twig', array(
			'title' 			=> 'Sign up',
			'section'			=> 'members',
			'data'				=> isset($data) ? $data : null
		) + $this->params);
	}

	public function logout ()
	{
		$this->app['session']->remove('user');

		return $this->app->redirect('/');
	}
}