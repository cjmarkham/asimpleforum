<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TopicModel extends BaseModel
{
	/**
	 * Silex App
	 * @var object
	 */
	public $app;

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

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;

		$this->collection = $this->app['cache']->setCollection($app['database']['name'], 'topics');
	}

	public function findById ($id) 
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		$cache_key = 'topic-' . $id;

		$topic = $this->app['cache']->get($cache_key, function () use ($id) {
			$data = [
				'data' => $this->app['db']->fetchAssoc('SELECT * FROM topics WHERE id=? LIMIT 1', [
					$id
				])
			];

			return $data;
		});

		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'views');

		$views = $this->app['cache']->get('topic-views-' . $topic['data']['id'], function () use ($topic) {
			$amount = [];

			for ($i = 0; $i < (int) $topic['data']['views']; $i++)
			{
				$amount[] = time();
			}

			$data = [
				'data' => $amount
			];

			return $data;
		});

		$topic['data']['views'] = count($views['data']);

		return $topic['data'];
	}

	public function findByName ($name)
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
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$this->app['cache']->collection = $this->collection;

		$cache_key = 'topics-forum-' . $forum_id . '.' . $offset;
		$topics = $this->app['cache']->get($cache_key, function () use ($forum_id, $offset) {
			$topics = $this->app['db']->fetchAll(
				'SELECT ' . 
					't.id, t.forum, t.lastAuthorId, t.name, t.views, t.replies, t.added, t.author, t.updated, t.sticky, t.locked, ' . 
					'p.name as lastPostName, p.id as lastPostId, p.content, ' . 
					'u.username as authorName, us.username as lastPosterUsername, ' . 
					'f.name as forumName ' . 
				'FROM topics t ' . 
				'JOIN users u ' . 
				'ON t.author=u.id ' . 
				'JOIN users us ' . 
				'ON us.id=t.lastAuthorId ' . 
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
						'id' => $topic['author'],
						'username' => $topic['authorName']
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
							'id' => $topic['lastAuthorId'],
							'username' => $topic['lastPosterUsername']
						)
					)
				);

				$data['data'][] = $_topic;
			}

			return $data;
		});

		return json_encode($topics);
	}

	public function updateViews (Request $request)
	{
		$id = (int) $request->get('id');

		if (!$id) 
		{
			return false;
		}

		$topic = $this->findById($id);
		if (!$topic)
		{
			return false;
		}

		$this->app['db']->update('topics', array(
			'views' => $topic['views'] + 1
		), array('id' => $id));

		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'views');
		
		$views = $this->app['cache']->append('topic-views-' . $topic['id'], time());
		
		return true;
	}

	public function findRecent($amount = 4)
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
					'u.id as lastAuthorId, u.username as lastAuthorUsername, ' .  
					'f.name as forumName, ' .  
					'p.name as lastPostName, p.content, ' . 
					'us.id as authorId, us.username as authorUsername ' . 
				'FROM topics t ' . 
				'JOIN posts p ' . 
				'ON p.id=t.lastPostId ' . 
				'JOIN users u ' .  
				'ON p.author=u.id ' .  
				'JOIN users us ' .  
				'ON t.author=us.id ' .  
				'JOIN forums f ' .  
				'ON t.forum=f.id ' .  
				'ORDER BY added ' . 
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
						'id' => $topic['authorId'],
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
							'id' => $topic['lastAuthorId'],
							'username' => $topic['lastAuthorUsername']
						)
					)
				);

				$data['data'][] = $_topic;
			}

			return $data;
		});

		return $topics['data'];
	}

	public function addTopic (Request $request)
	{
		$user = $this->app['session']->get('user');
		$response = new Response;

		if (!$user)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		if (!\ASF\Permissions::hasPermission('CREATE_TOPIC')) 
		{
			$response->setStatusCode(400);
	        $response->setContent($this->app->trans('NO_PERMISSION'));
	        return $response;
		}

		$forum_id = (int) $request->get('forumId');

		if (!$forum_id)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$forum = $this->app['forum']->findById($forum_id);

		if (!$forum)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
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
			$response->setContent($this->app->trans('FILL_ALL_FIELDS'));
			return $response;
		}

		$name = strip_tags($name);
		$content = strip_tags($content, implode(',', $this->allowed_html));
		$attachments = $request->files->get('attachments');

		if ($attachments[0] != null)
		{
			if (count($attachments) > 5)
			{
				$response->setStatusCode(400);
				$response->setContent($this->app->trans('TOO_MANY_ATTACHMENTS'));
				return $response;
			}

			foreach ($attachments as $attachment)
			{
				$attachment_name = $attachment->getClientOriginalName();
				$size = $attachment->getClientSize();
				$mime = $attachment->getClientMimeType();

				if ($size >= $this->app['files']['maxSize'])
				{
					$response->setStatusCode(400);
					$response->setContent($this->app->trans('FILE_TOO_BIG', array($attachment_name, ($this->app['files']['maxSize'] / 1024))));
					return $response;
				}

				$ext = pathinfo($attachment_name, PATHINFO_EXTENSION);

				if (!in_array($ext, $this->app['files']['types']))
				{
					$response->setStatusCode(400);
					$response->setContent($this->app->trans('INVALID_FILE_EXT', array($ext, implode(', ', $this->app['files']['types']))));
					return $response;
				}
			}
		}

		if (!\ASF\Permissions::hasPermission('BYPASS_RESTRICTIONS'))
		{
			$user_last = $this->app['db']->fetchAssoc('SELECT forum, added FROM topics WHERE author=? AND forum=? ORDER BY added DESC LIMIT 1', array(
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
				$response->setContent($this->app->trans('TOPIC_POST_LIMIT', array($minutes, $seconds)));
				return $response;
			}
		}

		$time = date('Y-m-d H:i:s');

		$this->app['db']->insert('topics', array(
			'forum' => $forum_id,
			'name' => $name,
			'author' => $user['id'],
			'locked' => $locked,
			'sticky' => $sticky,
			'added' => $time,
			'updated' => $time,
			'lastPostId' => 0,
			'lastAuthorId' => $user['id']
		));

		$topic_id = $this->app['db']->lastInsertId();

		// Format mentions
		$mentions = $this->app['post']->parseMentions($content);

		if ($mentions['has_mentions'])
		{
			$content = $mentions['content'];
		}

		$this->app['db']->insert('posts', array(
			'topic' => $topic_id,
			'forum' => $forum_id,
			'name' => $name,
			'content' => $content,
			'author' => $user['id'],
			'added' => $time,
			'updated' => $time
		));

		$post_id = $this->app['db']->lastInsertId();

		$post_url = '/' . $this->app['board']['base'] . urlencode($forum['name']) . '/' . urlencode($name) . '-' . $topic_id . '/#' . $post_id;

		if ($mentions['has_mentions'])
		{
			$content = $mentions['content'];

			foreach ($mentions['users'] as $mention)
			{
				$this->app['notification']->add(new Request([
					'user_id' => $mention['data']['id'],
					'notification' => '<a href="/' . $this->app['board']['base'] . 'user/' . $user['username'] . '/" class="user-link">' . $user['username'] . '</a> mentioned you in a <a href="' . $post_url . '">post</a>'
				]));
			}
		}

		// Upload attachments
		if ($attachments[0] != null)
		{
			$upload_dir = dirname(dirname(__DIR__)) . '/public/uploads/attachments';

			foreach ($attachments as $attachment)
			{
				$attachment_name = $attachment->getClientOriginalName();
				$file_name = time() . '-' . $attachment_name;

				$attachment->move($upload_dir, $file_name);

				if ($attachment->getError())
				{
					// Couldnt upload attachment, delete post
					$this->app['db']->delete('posts', array('id' => $post_id));
					
					$response->setStatusCode(500);
					$response->setContent($this->app->trans('COULDNT_UPLOAD_FILE', array($attachment_name)));
					return $response;
				}

				$this->app['db']->insert('attachments', array(
					'post_id' => $post_id,
					'name' => $attachment_name,
					'file_name' => $file_name,
					'size' => $attachment->getClientSize(),
					'mime' => $attachment->getClientMimeType(),
					'added' => time()
				));
			}
		}

		$this->app['db']->update('topics', array(
			'lastPostId' => $post_id
		), array('id' => $topic_id));

		$this->app['db']->executeQuery('UPDATE forums SET topics=topics+1, posts=posts+1, lastTopicId=?, lastAuthorId=?, lastPostTime=?, lastPostId=?, updated=? WHERE id=? LIMIT 1', array(
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

		$this->app['cache']->setCollection($this->app['database']['name'], 'forums');
		$this->app['cache']->delete('forum-' . $forum_id);
		$this->app['cache']->delete('forums.all');

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