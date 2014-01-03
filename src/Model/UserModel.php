<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserModel extends BaseModel
{
	/**
	 * Silex App
	 * @var object
	 */
	public $app;

	/**
	 * Set the silex app object
	 * @param SilexApplication $app
	 */
	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function find_by_username($username)
	{
		if (!$username)
		{
			return false;
		}

		$user = $this->app['db']->fetchAssoc('SELECT id,username,ip,regdate FROM users WHERE username=? LIMIT 1', array(
			$username
		));

		return $user;
	}

	public function find_comments ($user_id, $page, $per_page = 4)
	{
		$user_id = (int) $user_id;

		if (!$user_id)
		{
			return false;
		}

		$cache_key = 'profile-comment-count-' . $user_id;

		$total = $this->app['cache']->get($cache_key, function () use ($user_id) {
			$data = array(
				'data' => $this->app['db']->fetchColumn('SELECT COUNT(*) FROM profile_comments WHERE profile=?', array(
					$user_id
				))
			);

			return $data;
		});

		$comments['pagination'] = $this->pagination((int) $total['data'], (int) $per_page, $page);

		$cache_key = 'profile-comments-' . $user_id . '.' . $comments['pagination']['sql_text'];

		$comments['data'] = $this->app['cache']->get($cache_key, function () use ($comments, $user_id) {
			$data = array(
				'data' => $this->app['db']->fetchAll('SELECT p.*, u.username FROM profile_comments p LEFT JOIN users u ON u.id=p.author WHERE p.profile=? ORDER BY p.added ASC ' . $comments['pagination']['sql_text'], array(
					$user_id
				))
			);

			return $data;
		});

		foreach ($comments['data']['data'] as $key => $comment)
		{
			$cache_key = 'profile-comment-' . $comment['id'] . '-likes';

			$likes = $this->app['cache']->get($cache_key, function () use ($comments, $key, $comment) {
				$data = array(
					'data' => $this->app['db']->fetchAll('SELECT username FROM profile_comment_likes WHERE profile_comment=? ORDER BY added DESC', array(
						$comment['id']
					))
				);

				return $data;
			});

			if (!$likes['data'])
			{
				$comments['data']['data'][$key]['likes'] = array();
			} 
			else
			{
				foreach ($likes['data'] as $like)
				{
					$comments['data']['data'][$key]['likes'][] = $like['username'];
				}
			}
			
		}

		return $comments;
	}

	public function addComment (Request $request)
	{
		$response = new Response;

		$profile_id = (int) $request->get('profileId');
		$comment = $request->get('comment');

		$user = $this->app['session']->get('user');

		if (!$user)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app['language']->phrase('MUST_BE_LOGGED_IN'));
			return $response;
		}

		if (!$comment || strlen($comment) < 6)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('COMMENT_MIN_LENGTH', array(6)));
			return $response;
		}

		// Todo: Add time limit
		$time = time();

		$this->app['db']->insert('profile_comments', array(
			'profile' => $profile_id,
			'author' => $user['id'],
			'comment' => $comment,
			'added' => $time
		));

		$this->app['cache']->delete_group('profile-comments-' . $profile_id);

		return json_encode(array(
			'comment' => $comment,
			'added' => $time,
			'username' => $user['username'],
			'profileId' => $profile_id
		));
	}
}