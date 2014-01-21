<?php

namespace Controller;

class MembersController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{
		return $this->app['twig']->render('Members/index.twig', array(
			'title' 			=> 'Members',
			'section'			=> 'members'
		) + $this->extras);
	}
}