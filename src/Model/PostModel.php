<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;

class PostModel 
{
	public $app;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function find_by_topic ($topic_id)
	{
		$topic_id = (int) $topic_id;

		if (!$topic_id)
		{
			return false;
		}

		return $this->app['db']->fetchAll('SELECT p.*, u.username FROM posts p JOIN users u ON p.poster=u.id WHERE p.topic=?', array(
			$topic_id
		));

	}
}