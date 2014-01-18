<?php

namespace Controller;

class HomeController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{
		\Mailer::setTemplate('emailConfirmation', array(
			'username' => 'cjmarkham',
			'boardTitle' => $this->app['config']->board['name'],
			'boardUrl'   => $this->app['config']->board['url'],
			'confirmCode' => base64_encode('doddsey65@hotmail.com' . '- ' . 1)
		));

		\Mailer::send('doddsey65@hotmail.com', 'info@asimpleforum.com' 'Email confirmation');
	
		\Message::alert('Your account has been created but you will need to confirm your email address before logging in. Check your emails for details on how to do so.');

		$forums = $this->app['forum']->find_all();

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'index',
			'forums' 			=> $forums
		) + $this->extras);
	}
}