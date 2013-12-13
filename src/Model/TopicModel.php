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

	public function find_by_forum ($forum_id, $page = 1)
	{
		$forum_id = (int) $forum_id;
		
		if (!$forum_id)
		{
			return false;
		}

		$total = $this->app['db']->fetchColumn('SELECT COUNT(*) FROM topics WHERE forum=?', array($forum_id));

		$topics['pagination'] = $this->pagination($total, 10, $page);

		$topics['data'] = $this->app['db']->fetchAll('SELECT t.*, p.name as lastPostName, p.id as lastPostId, u.username as author, us.username as lastPosterUsername FROM topics t JOIN users u ON t.poster=u.id JOIN users us ON us.id=t.lastPosterId LEFT JOIN posts p ON t.lastPostId=p.id WHERE t.forum=? ORDER BY sticky DESC, updated DESC ' . $topics['pagination']['sql_text'], array(
			$forum_id
		));

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

		return $this->app['db']->fetchAll('SELECT t.*, u.username as lastPoster, f.name as forumName FROM topics t JOIN users u ON t.lastPosterId=u.id JOIN forums f ON t.forum=f.id ORDER BY updated DESC LIMIT ' . $amount);
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

		$content = \Utils::bbcode($content);
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
			'content' => nl2br($content),
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

		return json_encode(array(
			'topic_id' => $topic_id,
			'post_id' => $post_id,
			'content' => $content,
			'author' => $user['username'],
			'forum_name' => $forum['name']
		));
	}
}