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
		var_dump(base64_decode("ZG9kZHNleTY1QGhvdG1haWwuY29tLSAw"));
		$forums = $this->app['forum']->find_all();

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'index',
			'forums' 			=> $forums
		) + $this->extras);
	}
}