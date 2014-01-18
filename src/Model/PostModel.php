<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class PostModel extends BaseModel
{
	/**
	 * Silex App
	 * @var object
	 */
	public $app;

	private $collection;

	/**
	 * HTML allowed for post content
	 * @var array
	 */
	public $allowed_html = array(
		'<p>',
		'<br />',
		'<em>',
		'<i>',
		'<strong>',
		'<b>',
		'<abbr>',
		'<a>',
		'<hr />',
		'<img />',
		'<s>',
		'<ul>',
		'<ol>',
		'<li>',
		'<h1>',
		'<h2>',
		'<h3>',
		'<h4>',
		'<h5>',
		'<blockquote>',
		'<span>',
		'<div>'
	);

	/**
	 * Set the silex app object
	 * @param SilexApplication $app
	 */
	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
		$this->collection = $this->app['mongo']['default']->selectCollection($app['config']->database['name'], 'posts');
	}

	public function find_by_user (Request $request)
	{
		$user_id = (int) $request->get('user_id');
		$page = (int) $request->get('page');

		if (!$user_id)
		{
			return false;
		}

		$cache_key = 'user-posts-' . $user_id . '.' . $page;

		$posts['data'] = $this->app['cache']->get($cache_key, function () use ($user_id, $page) {
			$data = array(
				'data' => $this->app['db']->fetchAll('SELECT p.*, t.name as topicName, f.name as forumName, u.username FROM posts p JOIN users u ON p.poster=u.id JOIN topics t ON t.id=p.topic JOIN forums f ON f.id=t.forum WHERE p.poster=? ORDER BY added DESC LIMIT ' . (($page - 1)* 5) . ', 5', array(
					$user_id
				))
			);

			return $data;
		});

		// need to pull all posts from each topic just to find out which page this post will be on

		return json_encode($posts);
	}

	/**
	 * Get a list of posts for a topic
	 * @param  int  $topic_id
	 * @param  int $page    
	 * @return array The list of posts and pagination data
	 */
	public function findByTopic (Request $request)
	{
		$topic_id = (int) $request->get('topicId');
		$offset = (int) $request->get('offset');
		$page = (int) $request->get('page');

		if (!$topic_id)
		{
			return false;
		}

		$limit = 10;

		if ($page)
		{
			$limit = $page * $limit;
		}

		$topics = array();

		$this->app['cache']->collection = $this->collection;

		$cache_key = 'topic-posts-' . $topic_id . '.' . $offset . $limit;

		$posts = $this->app['cache']->get($cache_key, function () use ($topic_id, $offset, $limit) {
			$data = array(
				'data' => $this->app['db']->fetchAll('SELECT p.*, u.username, u.posts as userPosts, g.name as `group` FROM posts p JOIN users u ON p.poster=u.id JOIN groups g ON g.id=u.perm_group WHERE p.topic=? ORDER BY added ASC LIMIT ' . $offset . ', ' . $limit, array(
					$topic_id
				))
			);

			return $data;
		});

		foreach ($posts['data'] as $key => $post)
		{
			$posts['data'][$key]['likes'] = array();

			$cache_key = 'post-' . $post['id'] . '-likes';

			$likes = $this->app['cache']->get($cache_key, function () use ($posts, $key, $post) {
				$data = array(
					'data' => $this->app['db']->fetchAll('SELECT username FROM likes WHERE postId=? ORDER BY added DESC', array(
						$post['id']
					))
				);

				return $data;
			});

			foreach ($likes['data'] as $like)
			{
				$posts['data'][$key]['likes'][] = $like['username'];
			}
			
		}

		return json_encode($posts);
	}

	/**
	 * Adds a post to the database
	 * @param Request $request The request object
	 */
	public function add (Request $request)
	{
		$topic_id = (int) $request->get('topicId');
		$response = new Response();

		if (!$topic_id)
		{
	        $response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
	        return $response;
		}

		$topic = $this->app['topic']->find_by_id($topic_id);

		if (!$topic)
		{
	        $response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
	        return $response;
		}

		if ($topic['locked'] && !\Permissions::hasPermission('BYPASS_RESTRICTIONS'))
		{
			$response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('TOPIC_LOCKED'));
	        return $response;
		}

		$user = $this->app['session']->get('user');

		if (!$user)
		{
	        $response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('MUST_BE_LOGGED_IN'));
	        return $response;
		}

		if (!\Permissions::hasPermission('CREATE_POST')) 
		{
			$response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('NO_PERMISSION'));
	        return $response;
		}

		$name = $request->get('name');
		$content = $request->get('content');

		$constraints = new Assert\Collection(array(
			'name' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Length(array('min' => 4)),
				new Assert\Length(array('max' => 25))
			),
			'content' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Length(array('min' => 6))
			)
		));

		$data = array(
			'name' => $name,
			'content' => $content
		);

		$errors = $this->app['validator']->validateValue($data, $constraints);

		if (count($errors) > 0)
		{
			$response = new Response();
	        $response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase(\Message::error($errors[0]->getMessage())));
	        return $response;
		}
		
		$content = strip_tags($content, implode(',', $this->allowed_html));
		$time = time();

		$last = $this->app['db']->fetchAssoc('SELECT * FROM posts WHERE topic=? ORDER BY added DESC LIMIT 1', array(
			$topic_id
		));

		if ($last['poster'] == $user['id'])
		{
			if ($this->app['config']->board['double_post'] === 'merge')
			{
				preg_match('/<div class="update">(.*)<\/div>/s', $last['content'], $matches);

				if (count($matches) > 0)
				{
					$content = preg_replace(
						'/<div class="update">(.*)<\/div>/s',
						'<div class="update">' . "\n" . '$1' . "\n" . $content . '</div>',
						$last['content']
					);
				}
				else
				{
					$content = $last['content'] . '<div class="update"><h2>Update</h2>' . "\n" . $content . '</div>';
				}

				$this->app['db']->update('posts', array(
					'content' => $content,
					'updated' => time()
				), array('id' => $last['id']));

				$this->app['db']->update('topics', array(
					'updated' => $time
				), array('id' => $last['topic']));

				$this->app['db']->update('forums', array(
					'lastTopicId' => $topic['id'],
					'lastPosterId' => $user['id'],
					'lastPostTime' => $time,
					'lastPostId' => $last['id']
				), array('id' => $last['forum']));

				$response = new Response();
				$response->setContent(json_encode(array(
					'id' => $last['id'],
					'content' => $content,
					'updated' => true
				)));
				return $response;
			}
			else if ($this->app['config']->board['double_post'] === 'disallow')
			{
				$response = new Response;
				$response->setStatusCode(400);
				$response->setContent($this->app['language']->phrase('NO_DOUBLE_POST'));
				return $response;
			}
		}

		$this->app['db']->insert('posts', array(
			'topic' => $topic['id'],
			'forum' => $topic['forum'],
			'name' => $name,
			'content' => $content,
			'poster' => $user['id'],
			'added' => $time,
			'updated' => $time
		));

		$post_id = $this->app['db']->lastInsertId();

		$this->app['db']->update('topics', array(
			'replies' => $topic['replies'] + 1,
			'updated' => $time,
			'lastPosterId' => $user['id'],
			'lastPostId' => $post_id
		), array('id' => $topic_id));

		$this->app['db']->executeQuery('UPDATE forums SET posts=posts+1, lastTopicId=?,lastPosterId=?, lastPostTime=?, lastPostId=? WHERE id=? LIMIT 1', array(
			$topic['id'],
			$user['id'],
			$time,
			$post_id,
			$topic['forum']
		));

		$post_count = $this->app['db']->fetchColumn('SELECT COUNT(id) FROM posts WHERE topic=?', array(
			$topic_id
		));

		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['config']->database['name'], 'posts');
		$this->app['cache']->delete_group('topic-post-count-' . $topic_id);
		$this->app['cache']->delete_group('topic-posts-' . $topic_id);
		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['config']->database['name'], 'forums');
		$this->app['cache']->delete_group('forum-' . $topic['forum']);
		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['config']->database['name'], 'topics');
		$this->app['cache']->delete('topic-' . $topic_id);

		$page = (int) ceil($post_count / $this->app['config']->board['posts_per_page']);

		$this->app['db']->executeQuery('UPDATE users SET posts=posts+1 WHERE id=? LIMIT 1', array(
			$user['id']
		));

		return json_encode(array(
			'id' => $post_id,
			'username' => $user['username'],
			'userId' => $user['id'],
			'page' => $page
		));
	}

	/**
	 * Gets the first post for a topic
	 * @param  Request $request The request object
	 * @return string           The post content
	 */
	public function get_first (Request $request)
	{
		$topic_id = (int) $request->get('topicId');

		if (!$topic_id)
		{
			return false;
		}

		$content = $this->app['db']->fetchColumn('SELECT content FROM posts WHERE topic=? ORDER BY added ASC LIMIT 1', array(
			$topic_id
		));

		return \Utils::truncate($content, 200);
	}

	/**
	 * Reports a post
	 * @param  Request $request The request object
	 * @return string           The message
	 */
	public function report (Request $request)
	{
		$post_id = (int) $request->get('postId');

		if (!$post_id)
		{
			return false;
		}

		$user = $this->app['session']->get('user');

		if (!$user)
		{
			return false;
		}

		$reason = $request->get('reason');

		$this->app['db']->insert('reports', array(
			'type' => 'POST',
			'typeId' => $post_id,
			'reporter' => $user['id'],
			'reason' => $reason,
			'added' => time(),
			'ip' => $request->getClientIp()
		));

		return 'Your report has been logged and a member of the team will look into it shortly.';
	}

	/**
	 * Finds a post by its id
	 * @param  Request $request The request object
	 * @return string           json encoded post data
	 */
	public function find_by_id (Request $request)
	{
		$id = (int) $request->get('id');

		if (!$id)
		{
			return false;
		}

		$post = $this->app['db']->fetchAssoc('SELECT * FROM posts WHERE id=? LIMIT 1', array(
			$id
		));

		$post['content'] = nl2br($post['content']);

		return json_encode($post);
	}

	/**
	 * Updates a posts content
	 * @param  Request $request The request object
	 * @return string           json encoded content
	 */
	public function update (Request $request)
	{
		$id = (int) $request->get('id');
		$content = strip_tags($request->get('content'), implode(',', $this->allowed_html));

		if (!$content)
		{
			$response = new Response();
	        $response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('FILL_ALL_FIELDS'));
	        return $response;
		}

		$this->app['db']->executeQuery('UPDATE posts SET content=?, edits=edits+1, updated=? WHERE id=? LIMIT 1', array(
			$content,
			time(),
			$id
		));

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete_group('topic-posts-' . $post['topic']);

		return json_encode(array(
			'content' => $content
		));
	}

	/**
	 * Likes a post
	 * @param  Request $request The request object
	 * @return string           the json encoded list of likes
	 */
	public function like(Request $request)
	{
		$post_id = (int) $request->get('postId');
		$user = $this->app['session']->get('user');
		$response = new Response;

		if (!$user)
		{
			$response->setStatusCode(500);
	        $response->setContent($this->app['language']->phrase('MUST_BE_LOGGED_IN'));
	        return $response;
		}

		if (!$post_id)
		{
			$response->setStatusCode(500);
	        $response->setContent($this->app['language']->phrase('UNKNOWN_ERROR'));
	        return $response;
		}

		$likes = $this->app['db']->fetchAll('SELECT username FROM likes WHERE postId=?', array(
			$post_id
		));

		$_likes = array();

		foreach ($likes as $like)
		{
			$_likes[] = $like['username'];
		}

		if (in_array($user['username'], $_likes))
		{
			$response->setStatusCode(400);
	        $response->setContent($this->app['language']->phrase('ALREADY_LIKED'));
	        return $response;
		}

		$this->app['db']->insert('likes', array(
			'postId' => $post_id,
			'username' => $user['username'],
			'added' => time()
		));

		$_likes[] = $user['username'];

		$this->app['cache']->collection = $this->collection;
		$this->app['cache']->delete('post-' . $post_id . '-likes');

		return json_encode($_likes);

	}
}