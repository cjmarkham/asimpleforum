<?php

namespace Controller;

class DownloadController
{
	public $app;

	public function index()
	{ 
		return $this->app['twig']->render('Download/index.twig', array(
			'title' 			=> 'Download ASF',
			'section'			=> 'download'
		));
	}
}