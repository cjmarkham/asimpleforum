<?php

namespace Controller;

use Silex\Application;

class BaseController
{
	public $params = array();

	public function __construct(Application $app)
	{		
		$user = $app['session']->get('user');

		$this->params = array(
			'debug' 		 => $app['debug'],
			'user'			 => $user
		);
	}
}