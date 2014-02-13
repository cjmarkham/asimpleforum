<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class TermsController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{ 
		return $this->app['twig']->render('Terms/index.twig', array(
			'title' 			=> 'Terms',
			'section'			=> 'terms'
		) + $this->extras);
	}
}