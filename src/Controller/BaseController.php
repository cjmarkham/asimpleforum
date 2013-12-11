<?php

namespace Controller;

use Silex\Application;

class BaseController
{
	public $params = array();

	public function __construct(Application $app)
	{		
		$user = $app['session']->get('user');

		if (!empty($user) && $user['approved'] == 0) 
		{
			\Message::alert('LOGGED_IN_NOT_APPROVED');
		}

		$this->params = array(
			'debug' 		 => $app['debug'],
			'user'			 => $user
		);
	}
}