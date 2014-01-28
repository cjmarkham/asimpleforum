<?php

namespace Controller;

class Controller 
{
	public $app;

	public $extras = array();

	public function init()
	{
		$alerts = $this->app['alert']->findByDate(time());

		$this->extras['alerts'] = $alerts;

		$nav_links = $this->app['navLinks'];
		$this->extras['nav_links'] = $nav_links;
	}
}