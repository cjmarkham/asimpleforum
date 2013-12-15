<?php

namespace Controller;

class HomeController extends BaseController
{
	public $app;

	public function index()
	{
		$forums = $this->app['forum']->find_all();		

		return $this->app['twig']->render('Home/index.twig', array(
			'title' 			=> 'Home',
			'section'			=> 'index',
			'forums' 			=> $forums
		) + $this->params);
	}
}