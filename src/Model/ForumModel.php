<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;


class ForumModel 
{
	public $app;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function find_all ()
	{
		$forums = $this->app['cache']->get('forums-all', function () {
			return $this->app['db']->fetchAll('SELECT * FROM forums');
		});

		foreach ($forums as $key => $forum)
		{
			if ($forum['parent'] == 0)
			{
				$_forums[$forum['id']] = $forum;
			}
			else
			{
				$_forums[$forum['parent']]['children'][] = $forum;
			}
		}

		return $_forums;
	}
}