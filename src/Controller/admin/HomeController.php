<?php

namespace Controller\Admin;

class HomeController extends AdminController
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{
		return $this->app['twig']->render('Admin/Home/index.twig', array(
			'title' 			=> 'Admin',
			'section'			=> 'admin/index'
		) + $this->extras);
	}
}