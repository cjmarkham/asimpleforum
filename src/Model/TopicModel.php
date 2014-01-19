<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TopicModel extends BaseModel
{
	public $app;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
		$this->collection = $this->app['mongo']['default']->selectCollection($app['config']->database['name'], 'topics');
	}

	public function find_by_id ($id) 
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		$cache_key = 'topic-' . $id;

		$topic = $this->app['cache']->get($cache_key, function () use ($id) {
			$data = array(
				'data' => $this->app['db']->fetchAssoc('SELECT * FROM topics WHERE id=? LIMIT 1', array(
					$id
				))
			);

			return $data;
		});

		return $topic['data'];
	}

	public function find_by_name ($name)
	{
		if (!$name)
		{
			return false;
		}

		$cache_key = 'topic-' . $name;

		$topic = $this->app['cache']->get($cache_key, function () use ($name) {
			$data = array(
				'data' => $this->app['db']->fetchAssoc('SELECT t.id, t.name, f.name as forumName FROM topics t JOIN forums f ON f.id=t.forum WHERE t.name=? LIMIT 1', array(
					$name
				))
			);

			return $data;
		});

		return $topic['data'];
	}

	public function findByForum (Request $request)
	{
		$forum_id = (int) $request->get('forumId');
		$offset = (int) $request->get('offset');

		if (!$forum_id)
		{
			$response = new Response();
			$response->setStatusCode(500);
			$response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
			return $response;
		}

		$this->app['cache']->collection = $this->collection;

		$cache_key = 'topics-forum-' . $forum_id . '.' . $offset;
		$topics = $this->app['cache']->get($cache_key, function () use ($forum_id, $offset) {
			$topics = $this->app['db']->fetchAll(
				'SELECT ' . 
					't.*, ' . 
					'p.name as lastPostName, p.id as lastPostId, p.content, ' . 
					'u.username as author, us.username as lastPosterUsername, ' . 
					'f.name as forumName ' . 
				'FROM topics t ' . 
				'JOIN users u ' . 
				'ON t.poster=u.id ' . 
				'JOIN users us ' . 
				'ON us.id=t.lastPosterId ' . 
				'LEFT JOIN posts p ' . 
				'ON t.lastPostId=p.id ' . 
				'JOIN forums f ' . 
				'ON f.id=t.forum ' . 
				'WHERE t.forum=? ' . 
				'ORDER BY sticky DESC, updated DESC ' . 
				'LIMIT ' . $offset . ', 10', 
				array(
					$forum_id
				)
			);

			$data = array('data' => array());

			foreach ($topics as $topic)
			{
				$_topic = array(
					'id' => $topic['id'],
					'name' => $topic['name'],
					'views' => $topic['views'],
					'replies' => $topic['replies'],
					'added' => $topic['added'],
					'updated' => $topic['updated'],
					'sticky'	=> $topic['sticky'],
					'locked'	=> $topic['locked'],
					'author' => array(
						'id' => $topic['poster'],
						'username' => $topic['author']
					),
					'forum' => array(
						'id' => $topic['forum'],
						'name' => $topic['forumName']
					),
					'lastPost' => array(
						'id' => $topic['lastPostId'],
						'name' => $topic['lastPostName'],
						'content' => $topic['content'],
						'user' => array(
							'id' => $topic['lastPosterId'],
							'username' => $topic['author']
						)
					)
				);

				$data['data'][] = $_topic;
			}

			return $data;
		});

		return json_encode($topics);
	}

	public function update_views (Request $request)
	{
		$id = (int) $request->get('id');

		if (!$id) 
		{
			return false;
		}

		$topic = $this->find_by_id($id);

		if (!$topic)
		{
			return false;
		}

		$this->app['db']->update('topics', array(
			'views' => $topic['views'] + 1
		), array('id' => $id));

		$this->app['cache']->collection = $this->collection;

		$this->app['cache']->delete('topic-' . $topic['name']);
		$this->app['cache']->delete('topic-' . $topic['id']);

		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['config']->database['name'], 'views');
		
		$views = $this->app['cache']->append('topic-views-54', time());
		
		return true;
	}

	public function find_recent($amount = 4)
	{
		$amount = (int) $amount;

		if (!$amount)
		{
			$amount = 4;
		}

		$cache_key = 'topics-recent.' . $amount;

		$this->app['cache']->collection = $this->collection;

		$topics = $this->app['cache']->get($cache_key, function () use ($amount) {

			$topics = $this->app['db']->fetchAll(
				'SELECT ' .  
					't.*, ' .  
					'u.id as lastPosterId, u.username, ' .  
					'f.name as forumName, ' .  
					'p.name as lastPostName, p.content, ' . 
					'us.id as authorId, us.username as authorUsername ' . 
				'FROM topics t ' . 
				'JOIN posts p ' . 
				'ON p.id=t.lastPostId ' . 
				'JOIN users u ' .  
				'ON p.poster=u.id ' .  
				'JOIN users us ' .  
				'ON t.poster=us.id ' .  
				'JOIN forums f ' .  
				'ON t.forum=f.id ' .  
				'ORDER BY updated ' . 
				'DESC LIMIT ' . $amount
			);

			$data = array('data' => array());

			foreach ($topics as $topic)
			{
				$_topic = array(
					'id' => $topic['id'],
					'name' => $topic['name'],
					'views' => $topic['views'],
					'replies' => $topic['replies'],
					'added' => $topic['added'],
					'updated' => $topic['updated'],
					'sticky'	=> $topic['sticky'],
					'locked'	=> $topic['locked'],
					'author' => array(
						'id' => $topic['poster'],
						'username' => $topic['authorUsername']
					),
					'forum' => array(
						'id' => $topic['forum'],
						'name' => $topic['forumName']
					),
					'lastPost' => array(
						'id' => $topic['lastPostId'],
						'name' => $topic['lastPostName'],
						'content' => $topic['content'],
						'user' => array(
							'id' => $topic['lastPosterId'],
							'username' => $topic['username']
						)
					)
				);

				$data['data'][] = $_topic;
			}

			return $data;
		});

		return $topics['data'];
	}

	public function add_topic (Request $request)
	{
		$user = $this->app['session']->get('user');
		$response = new Response;

		if (!$user)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('MUST_BE_LOGGED_IN'));
			return $response;
		}

		if (!\Permissions::hasPermission('CREATE_TOPIC')) 
		{
			$response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('NO_PERMISSION'));
	        return $response;
		}

		$forum_id = (int) $request->get('forumId');

		if (!$forum_id)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
			return $response;
		}

		$forum = $this->app['forum']->find_by_id($forum_id);

		if (!$forum)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
			return $response;
		}

		$name = $request->get('title');
		$content = $request->get('content');
		$locked = $request->get('locked');
		$sticky = $request->get('sticky');

		if (!$locked)
		{
			$locked = 0;
		}

		if (!$sticky)
		{
			$sticky = 0;
		}

		if (!$name || !$content)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('FILL_ALL_FIELDS'));
			return $response;
		}

		$name = strip_tags($name);

		if (!\Permissions::hasPermission('BYPASS_RESTRICTIONS'))
		{
			$user_last = $this->app['db']->fetchAssoc('SELECT forum, added FROM topics WHERE poster=? AND forum=? ORDER BY added DESC LIMIT 1', array(
				$user['id'],
				$forum_id
			));

			$time_since_last = time() - (int) $user_last['added'];
			
			if ($time_since_last < 300)
			{
				$seconds = 300 - $time_since_last;
				$minutes = round($seconds / 60);
				$seconds = $seconds % 60;
				$response->setStatusCode(403);
				$response->setContent($this->app['language']->phrase('TOPIC_POST_LIMIT', array($minutes, $seconds)));
				return $response;
			}
		}

		$time = time();

		$this->app['db']->insert('topics', array(
			'forum' => $forum_id,
			'name' => $name,
			'poster' => $user['id'],
			'locked' => $locked,
			'sticky' => $sticky,
			'added' => $time,
			'updated' => $time,
			'lastPostId' => 0,
			'lastPosterId' => $user['id']
		));

		$topic_id = $this->app['db']->lastInsertId();

		$this->app['db']->insert('posts', array(
			'topic' => $topic_id,
			'forum' => $forum_id,
			'name' => $name,
			'content' => $content,
			'poster' => $user['id'],
			'added' => $time,
			'updated' => $time
		));

		$post_id = $this->app['db']->lastInsertId();

		$this->app['db']->update('topics', array(
			'lastPostId' => $post_id
		), array('id' => $topic_id));

		$this->app['db']->executeQuery('UPDATE forums SET topics=topics+1, posts=posts+1, lastTopicId=?, lastPosterId=?, lastPostTime=?, lastPostId=?, updated=? WHERE id=? LIMIT 1', array(
			$topic_id,
			$user['id'],
			$time,
			$post_id,
			$time,
			$forum_id
		));

		$this->app['db']->executeQuery('UPDATE users SET topics=topics+1, posts=posts+1 WHERE id=? LIMIT 1', array(
			$user['id']
		));

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete_group('topics-recent');
		$this->app['cache']->delete_group('topics-forum-' . $forum_id);

		return json_encode(array(
			'id' => $topic_id,
			'topic_id' => $topic_id,
			'post_id' => $post_id,
			'content' => $content,
			'author' => $user['username'],
			'forum_name' => $forum['name'],
			'locked' => $locked,
			'sticky' => $sticky
		));
	}
}