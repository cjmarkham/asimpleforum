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

		$this->app['cache']->setCollection($this->app['database']['name'], 'groups');

		$group = $this->app['cache']->get('group-' . $id, function () use ($id) {
			$data = [
				'data' => $this->app['db']->fetchAssoc('SELECT * FROM groups WHERE id=? LIMIT 1',[
					$id
				])
			];

			return $data;
		});

		return $group['data'];
	}
}