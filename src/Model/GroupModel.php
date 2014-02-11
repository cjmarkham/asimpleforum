<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupModel extends BaseModel
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

	public function findById ($id)
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		return $this->app['db']->fetchAssoc('SELECT * FROM groups WHERE id=? LIMIT 1', array(
			$id
		));
	}
}