<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;


class ForumModel 
{
	public $app;
	private $collection;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
		$this->collection = $this->app['mongo']['default']->selectCollection($app['database']['name'], 'forums');
	}

	public function find_by_id ($id)
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		$cache_key = 'forum-' . $id;
		$this->app['cache']->collection = $this->collection;

		$forum = $this->app['cache']->get($cache_key, function () use ($id) {
			$data = array(
				'data' => $this->app['db']->fetchAssoc('SELECT * FROM forums WHERE id=? LIMIT 1', array(
					$id
				))
			);

			return $data;
		});

		return $forum['data'];
	}

	public function find_all ()
	{
		$cache_key = 'forums.all';

		$this->app['cache']->collection = $this->collection;

		// Look for data in cache
		$forums = $this->app['cache']->get($cache_key, function () {

			// If not in cache get from mysql
			$forums = $this->app['db']->fetchAll(
				'SELECT ' . 
					'f.*, ' . 
					't.name as lastTopicName, t.id as lastTopicId, ' . 
					'u.username, u.regdate, u.ip, u.lastActive, ' . 
					'p.id as lastPostId, p.name as lastPostName, p.content ' . 
				'FROM forums f ' . 
				'LEFT JOIN topics t ' . 
				'ON f.lastTopicId=t.id ' . 
				'LEFT JOIN users u ' . 
				'ON f.lastPosterId=u.id ' . 
				'LEFT JOIN posts p ' . 
				'ON f.lastPostId=p.id'
			);

			$data = array('data' => array());

			// Build collection object
			foreach ($forums as $key => $forum)
			{
				$_forum = array(
					'id' => $forum['id'],
					'parent' => $forum['parent'],
					'name' => $forum['name'],
					'description' => $forum['description'],
					'updated' => $forum['updated'],
					'lastTopic' => array(
						'id' => $forum['lastTopicId'],
						'name' => $forum['lastTopicName']
					),
					'lastPost' => array(
						'id' => $forum['lastPostId'],
						'name' => $forum['lastPostName'],
						'content' => $forum['content'],
						'user' => array(
							'username' => $forum['username'],
							'regdate' => $forum['regdate'],
							'ip' => $forum['ip'],
							'lastActive' => $forum['lastActive']
						)
					)
				);

				// A parent forum
				if ($forum['parent'] == 0)
				{
					$data['data'][$forum['id']] = $_forum;
					unset($forums[$key]);
				}
				else
				{
					// If this forum has a parent already set
					if (isset($data['data'][$forum['parent']]))
					{
						$data['data'][$forum['parent']]['children'][$forum['id']] = $_forum;
						unset($forums[$key]);
					}
				}
			}

			// if we still have forums then these are sub forums
			if (count($forums))
			{
				foreach ($forums as $key => $forum)
				{
					$parent_id = $forum['parent'];

					foreach ($data['data'] as $forum_id => $_forum)
					{
						foreach ($_forum['children'] as $id => $child)
						{
							if ($id == $parent_id)
							{
								$data['data'][$forum_id]['children'][$parent_id]['children'][] = $forum;
							}
						}
					}
				}
			}

			// return data to be inserted into mongo
			return $data;

		});
	
		// return cached data
		return $forums['data'];
	}
}