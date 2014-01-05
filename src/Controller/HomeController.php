<?php

namespace Controller;

class HomeController
{
	public $app;

	public function index()
	{
		$forums = $this->app['forum']->find_all();

		\Mailer::setTemplate('emailConfirmation', array(
			'username' => 'cjmarkham'
		));

		\Mailer::send('doddsey65@hotmail.com', 'no-reply@asimpleforum.com', 'Email confirmation');

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'index',
			'forums' 			=> $forums
		));
	}
}