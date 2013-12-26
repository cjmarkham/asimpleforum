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

	public function find_by_forum ($forum_id, $page = 1)
	{
		$forum_id = (int) $forum_id;
		
		if (!$forum_id)
		{
			return false;
		}

		$cache_key = 'forum-topic-count-' . $forum_id;
		$this->app['cache']->collection = $this->collection;

		$total = $this->app['cache']->get($cache_key, function () use ($forum_id) {
			$data = array(
				'data' => $this->app['db']->fetchColumn('SELECT COUNT(*) FROM topics WHERE forum=?', array($forum_id))
			);

			return $data;
		});

		$topics['pagination'] = $this->pagination((int) $total['data'], $this->app['config']->board['topics_per_page'], $page);

		$cache_key = 'forum-topics-' . $forum_id . '.' . $topics['pagination']['sql_text'];

		$topics['data'] = $this->app['cache']->get($cache_key, function () use ($topics, $forum_id) {
			$data = array(
				'data' => $this->app['db']->fetchAll('SELECT t.*, p.name as lastPostName, p.id as lastPostId, u.username as author, us.username as lastPosterUsername FROM topics t JOIN users u ON t.poster=u.id JOIN users us ON us.id=t.lastPosterId LEFT JOIN posts p ON t.lastPostId=p.id WHERE t.forum=? ORDER BY sticky DESC, updated DESC ' . $topics['pagination']['sql_text'], array(
					$forum_id
				))
			);

			return $data;
		});

		return $topics;
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
					'p.name as lastPostName, p.content ' . 
				'FROM topics t ' . 
				'JOIN posts p ' . 
				'ON p.id=t.lastPostId ' . 
				'JOIN users u ' .  
				'ON p.poster=u.id ' .  
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
					'updated' => $topic['updated'],
					'forum' => array(
						'id' => $topic['forum'],
						'name' => $topic['forumName']
					),
					'lastPost' => array(
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

		if (!$name || !$content)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('FILL_ALL_FIELDS'));
			return $response;
		}

		$user_last = $this->app['db']->fetchAssoc('SELECT forum, added FROM topics WHERE poster=? LIMIT 1', array(
			$user['id']
		));

		if ($user_last['forum'] == $forum_id && $user_last['added'] < time() - 300)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app['language']->phrase('TOPIC_POST_LIMIT'));
			return $response;
		}

		$content = $content;
		$time = time();

		$this->app['db']->insert('topics', array(
			'forum' => $forum_id,
			'name' => $name,
			'poster' => $user['id'],
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

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete_group('topics-recent');
		$this->app['cache']->delete('forum-topic-count-' . $forum_id);
		$this->app['cache']->delete_group('forum-topics-' . $forum_id);

		return json_encode(array(
			'topic_id' => $topic_id,
			'post_id' => $post_id,
			'content' => $content,
			'author' => $user['username'],
			'forum_name' => $forum['name']
		));
	}
}