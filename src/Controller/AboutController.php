<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class AboutController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{ 
		return $this->app['twig']->render('About/index.twig', array(
			'title' 			=> 'About',
			'section'			=> 'about'
		) + $this->extras);
	}
}