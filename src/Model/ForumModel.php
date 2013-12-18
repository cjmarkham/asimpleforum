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
		$collection = $this->app['mongo']['default']->selectCollection('asf_forum', 'forums');
		$cache_key = 'forums.all';

		// Look for data in mongo
		$forums = $this->app['mongocache']->get($collection, $cache_key, function () {

			// If not in mongo get from mysql
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
			foreach ($forums as $forum)
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

				if ($forum['parent'] == 0)
				{
					$data['data'][$forum['id']] = $_forum;
				}
				else
				{
					$data['data'][$forum['parent']]['children'][] = $_forum;
				}
			}

			// return data to be inserted into mongo
			return $data;

		});
	
		// return cached data
		return $forums['data'];
	}
}