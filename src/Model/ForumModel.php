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

	public function find_by_id ($id)
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		return $this->app['db']->fetchAssoc('SELECT * FROM forums WHERE id=? LIMIT 1', array(
			$id
		));
	}

	public function find_all ()
	{

		$forums = $this->app['db']->fetchAll(
			'SELECT f.*, t.name as lastTopicName, u.username as lastPoster ' . 
			'FROM forums f ' . 
			'LEFT JOIN topics t ' . 
			'ON f.lastTopicId=t.id ' . 
			'LEFT JOIN users u ' . 
			'ON f.lastPosterId=u.id ' . 
			'LEFT JOIN posts p ' . 
			'ON f.lastPostId=p.id'
		);

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