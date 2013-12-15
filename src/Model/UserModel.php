<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserModel extends BaseModel
{
	/**
	 * Silex App
	 * @var object
	 */
	public $app;

	/**
	 * Set the silex app object
	 * @param SilexApplication $app
	 */
	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function find_by_username($username)
	{
		if (!$username)
		{
			return false;
		}

		$user = $this->app['db']->fetchAssoc('SELECT id,username,ip,regdate FROM users WHERE username=? LIMIT 1', array(
			$username
		));

		return $user;
	}
}