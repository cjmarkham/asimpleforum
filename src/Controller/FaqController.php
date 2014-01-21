<?php

namespace Controller;

class FaqController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{
		return $this->app['twig']->render('Faq/index.twig', array(
			'title' 			=> 'Faq',
			'section'			=> 'faq'
		) + $this->extras);
	}
}