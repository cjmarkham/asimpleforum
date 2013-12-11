<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;


class TopicModel 
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

		return $this->app['db']->fetchAssoc('SELECT * FROM topics WHERE id=? LIMIT 1', array(
			$id
		));
	}

	public function find_by_forum ($forum_id)
	{
		$forum_id = (int) $forum_id;
		
		if (!$forum_id)
		{
			return false;
		}

		$topics = $this->app['db']->fetchAll('SELECT t.*, p.name as lastPostName, p.id as lastPostId, u.username as author, us.username as lastPosterUsername FROM topics t JOIN users u ON t.poster=u.id JOIN users us ON us.id=t.lastPosterId LEFT JOIN posts p ON t.lastPostId=p.id WHERE t.forum=?', array(
			$forum_id
		));

		return $topics;
	}
}