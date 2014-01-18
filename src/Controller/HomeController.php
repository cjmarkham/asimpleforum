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
		var_dump($this->app['config']->board['confirmEmail']);
		$forums = $this->app['forum']->find_all();

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'index',
			'forums' 			=> $forums
		) + $this->extras);
	}
}