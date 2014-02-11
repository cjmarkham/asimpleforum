<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ForumModel 
{
	public $app;
	private $collection;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;

		$this->collection = $this->app['cache']->setCollection($app['database']['name'], 'forums');
	}

	public function add (Request $request)
	{
		$parent = (int) $request->get('parent');

		$parent = $this->findById($parent);

		if (!$parent)
		{
			return new Response($this->app['language']->phrase('UNKNOWN_ERROR'), 500);
		}

		$name = $request->get('name');
		$description = $request->get('description');

		if (!$description)
		{
			$description = null;
		}

		$response = new Response;

		if (!$name)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('FILL_ALL_FIELDS'));
			return $response;
		}

		$left = $parent['left'];
		$right = $parent['right'];

		$this->app['db']->insert('forums', array(
			'name' => $name,
			'parent' => $parent['id'],
			'`left`' => $parent['right'],
			'`right`' => $parent['right'] + 1,
			'added' => time(),
			'updated' => time()
		));

		$id = $this->app['db']->lastInsertId();

		if (!$id)
		{
			return new Response($this->app['language']->phrase('UNKNOWN_ERROR'), 500);
		}

		$this->app['db']->executeQuery('UPDATE forums SET `right`=`right`+2 WHERE `right`>=? AND id!=?', array(
			$left,
			$id
		));

		$this->app['db']->executeQuery('UPDATE forums SET `left`=`left`+2 WHERE `left`>=? AND id!=?', array(
			$right,
			$id
		));

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete_group('forums');

		return true;
	}

	public function delete (Request $request)
	{
		$forum_id = (int) $request->get('id');

		if (!$forum_id)
		{
			return false;
		}

		$forum = $this->findById($forum_id);

		$this->app['db']->executeQuery('UPDATE forums SET `right`=`right`-2 WHERE `right`>?', array(
			$forum['left']
		));

		$this->app['db']->executeQuery('UPDATE forums SET `left`=`left`-2 WHERE `left`>?', array(
			$forum['right']
		));

		$this->app['db']->delete('forums', array('id' => $forum_id));

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete_group('forums');

		return true;
	}

	public function findById ($id)
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

	public function findAll ($root_data = false)
	{
		$parent_id = 0;
		$forums = array();
		$subforums = array();
		$sql_where = '';
		$parents = array();

		if (!$root_data)
		{
			$root_data = array('forum_id' => 0);
		}
		else
		{
			$sql_where = '`left` > ' . $root_data['left'] . ' AND `left` < ' . $root_data['right'];
		}

		$results = $this->app['db']->fetchAll('SELECT * FROM forums ' . $sql_where . ' ORDER BY `left` ASC');

		$branch_root = $root_data['forum_id'];

		// Get all the parent nodes
		foreach ($results as $key => $value)
		{
			$forum_id = $value['id'];

			if ($value['parent'] == $root_data['forum_id'] || $value['parent'] == $branch_root)
			{
				$parent_id = $forum_id;

				$forums[$forum_id] = $value;

				if ($value['parent'] == $root_data['forum_id'])
				{
					$branch_root = $forum_id;
				}
			}
			else
			{
				$subforums[$parent_id][$forum_id] = $value;
			}
		}

		foreach ($forums as $key => $value)
		{
			// Category
			if ($value['parent'] == $root_data['forum_id'])
			{
				$parents[$value['id']] = $value;

				continue;
			}

			$forum_id = $value['id'];

			$subforums_list = array();

			// Get list of all subforums
			if (isset($subforums[$forum_id]))
			{
				foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
				{
					if (isset($subforum_row['name']))
					{
						$subforums_list[] = $subforum_row;
					}
					else
					{
						unset($subforums[$forum_id][$subforum_id]);
					}
				}
			}

			$parents[$value['parent']]['forums'][$value['id']] = $value;
			if (isset($subforums[$value['id']]))
			{
				$parents[$value['parent']]['forums'][$value['id']]['forums'] = $subforums[$value['id']];
			}
		}

		return $parents;
	}

	public function findAll ()
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
				'ON f.lastPostId=p.id ' . 
				'ORDER BY parent ASC, display ASC'
			);

			$data = array('data' => array());

			// Build collection object
			foreach ($forums as $key => $forum)
			{
				$_forum = array(
					'id' => $forum['id'],
					'parent' => $forum['parent'],
					'name' => $forum['name'],
					'topics' => $forum['topics'],
					'posts' => $forum['posts'],
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
						if (isset($_forum['children']))
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
			}

			// return data to be inserted into mongo
			return $data;

		});
	
		// return cached data
		return $forums['data'];
	}
}