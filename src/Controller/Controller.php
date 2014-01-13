<?php

namespace Controller;

class Controller 
{
	public $app;

	public $extras = array();

	public function init()
	{
		$nav_links_file = dirname(dirname(__DIR__)) . '/config/' . $this->app['config']->base . '/navLinks.json';
		$nav_links = new \File($nav_links_file);
		$nav_links->read()->jsonDecode();

		$this->extras['nav_links'] = $nav_links->getContent();
	}
}