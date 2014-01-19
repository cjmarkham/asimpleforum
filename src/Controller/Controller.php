<?php

namespace Controller;

class Controller 
{
	public $app;

	public $extras = array();

	public function init()
	{
		$nav_links = $this->app['navLinks'];
		$this->extras['nav_links'] = $nav_links;
	}
}