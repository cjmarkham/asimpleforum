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
		$forums = $this->app['forum']->findAll();

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'forums',
			'forums' 			=> $forums
		) + $this->extras);
	}
}