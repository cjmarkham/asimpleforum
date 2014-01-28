<?php

namespace Controller\Admin;

class ForumsController extends AdminController
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{
		$forums = $this->app['forum']->findAll();

		return $this->app['twig']->render('Admin/Forums/index.twig', array(
			'title' 			=> 'Admin',
			'section'			=> 'admin/forums',
			'forums'			=> $forums
		) + $this->extras);
	}
}