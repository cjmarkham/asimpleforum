<?php

namespace Controller;

class DownloadController extends Controller
{
	public $app;

	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index()
	{ 
		return $this->app['twig']->render('Download/index.twig', array(
			'title' 			=> 'Download ASF',
			'section'			=> 'download'
		) + $this->extras);
	}
}