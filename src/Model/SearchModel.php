<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;


class SearchModel 
{
	public $app;

	public function __construct (\ASFApplication $app)
	{
		$this->app = $app;
	}

	public function users ($query)
	{
		return $this->app['db']->fetchAll('SELECT u.*, p.* FROM users u JOIN profiles p ON p.id=u.id WHERE u.username LIKE ? LIMIT 10', array(
			'%' . $query . '%'
		));
	}

	public function get ($query, $selection)
	{
		$forum_sql = 'SELECT ' . 
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
					'ON f.lastPostId=p.id WHERE f.name LIKE ? or f.description LIKE ? AND f.parent > 0 LIMIT 10';

		$topic_sql = 'SELECT t.*, f.name as forumName, p.name as lastPostName, p.id as lastPostId, u.username as author, us.username as lastPosterUsername FROM topics t JOIN forums f ON f.id=t.forum JOIN users u ON t.poster=u.id JOIN users us ON us.id=t.lastPosterId LEFT JOIN posts p ON t.lastPostId=p.id WHERE t.name LIKE ? ORDER BY sticky DESC, updated DESC LIMIT 10';

		$user_sql = 'SELECT username,regdate,email FROM users WHERE username LIKE ? LIMIT 10';

		$forums = $this->app['db']->fetchAll($forum_sql, ['%' . $query . '%', '%' . $query . '%']);
		$topics = $this->app['db']->fetchAll($topic_sql, ['%' . $query . '%']);
		$users  = $this->app['db']->fetchAll($user_sql, ['%' . $query . '%']);

		$data = [
			'forums' => $forums,
			'topics' => $topics,
			'users'  => $users
		];

		return $data;
	}

	public function typeahead ($query, $selection) 
	{
		$where = 'WHERE t.name LIKE ?';
		$params = [
			'%' . $query . '%'
		];
		$data = [];
		$columns = 't.name, f.name as forumName';
		$join = 'JOIN forums f ON f.id=t.forum';

		if ($selection != 'null')
		{
			$where .= ' AND t.forum=?';
			$params[] = $selection;
		}

		$sql = 'SELECT ' . $columns . ' FROM topics t ' . $join . ' ' . $where . ' LIMIT 6';

		$results = $this->app['db']->fetchAll($sql, $params);

		if ($results)
		{
			foreach ($results as $key => $result)
			{
				$data[$key]['name'] = $result['name'];

				$data[$key]['forum'] = $result['forumName'];
			}
		}

		return $data;
	}
}