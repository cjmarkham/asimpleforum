<?php

namespace Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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

		$this->app['cache']->collection = $this->app['cache']->setCollection($app['database']['name'], 'posts');
	}

	/**
	 * Finds all users
	 * @return array
	 */
	public function findAll (Request $request)
	{
		$offset = (int) $request->get('offset');
		$users = [];

		$ids = $this->app['db']->fetchAll('SELECT id FROM users ORDER BY username ASC LIMIT ' . $offset . ', 20');

		foreach ($ids as $id)
		{
			$users[] = $this->findById($id['id'])['data'];
		}

		return json_encode($users);
	}

	public function saveAvatar (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$avatar = $request->files->get('avatar');

		$name = $avatar->getClientOriginalName();
		$size = $avatar->getClientSize();

		if ($size > $this->app['files']['maxSize'])
		{
			return new Response($this->app->trans('FILE_TOO_BIG', array($name, ($this->app['files']['maxSize'] / 1024))), 400);
		}

		$ext = pathinfo($name, PATHINFO_EXTENSION);

		if (!in_array($ext, $this->app['files']['types']))
		{
			return new Response($this->app->trans('INVALID_FILE_EXT', array($ext, implode(', ', $this->app['files']['types']))), 400);
		}

		$upload_dir = dirname(dirname(__DIR__)) . '/public/uploads/avatars/' . $user['username'];

		$this->app['imagine']
            ->open($avatar->getPathname())
            ->resize(new \Imagine\Image\Box(35, 35))
            ->save($upload_dir . '/tiny.png');

		$this->app['imagine']
        	->open($avatar->getPathname())
            ->resize(new \Imagine\Image\Box(55, 55))
            ->save($upload_dir . '/small.png');

        $this->app['imagine']
            ->open($avatar->getPathname())
            ->resize(new \Imagine\Image\Box(72, 72))
            ->save($upload_dir . '/medium.png');

        $this->app['imagine']
            ->open($avatar->getPathname())
            ->resize(new \Imagine\Image\Box(100, 100))
            ->save($upload_dir . '/large.png');

        return true;
	}

	/**
	 * Saves the users email change
	 * @param  Request $request The request object
	 * @return bool|Response
	 */
	public function saveEmail (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$email = $request->get('email');

		$constraints = [
			new Assert\Email([
				'message' => 'MUST_BE_EMAIL'
			]),
			new Assert\NotBlank([
				'message' => 'CANNOT_BE_BLANK'
			])
		];

		$errors = $this->app['validator']->validateValue($email, $constraints);

		if (count($errors))
		{
			return new Response($this->app->trans($errors[0]->getMessage()), 400);
		}

		$this->app['db']->update('users', [
			'email' => $email
		], ['id' => $user['id']]);

		return true;
	}

	/**
	 * Saves the users name change
	 * @param  Request $request The request object
	 * @return bool|Response
	 */
	public function saveDateFormat (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$format = $request->get('format');

		$errors = $this->app['validator']->validateValue($format, new Assert\NotBlank([
			'message' => 'CANNOT_BE_BLANK'
		]));

		if (count($errors))
		{
			return new Response($this->app->trans($errors[0]->getMessage()), 400);
		}

		$this->app['db']->update('settings', [
			'date_format' => $format
		], ['id' => $user['id']]);

		return true;
	}

	/**
	 * Saves the users name change
	 * @param  Request $request The request object
	 * @return bool|Response
	 */
	public function saveName (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$name = $request->get('name');

		$errors = $this->app['validator']->validateValue($name, new Assert\NotBlank([
			'message' => 'CANNOT_BE_BLANK'
		]));

		if (count($errors))
		{
			return new Response($this->app->trans($errors[0]->getMessage()), 400);
		}

		$this->app['db']->update('profiles', [
			'name' => $name
		], ['id' => $user['id']]);

		return true;
	}

	/**
	 * Saves the users location change
	 * @param  Request $request The request object
	 * @return bool|Response
	 */
	public function saveLocation (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$location = $request->get('location');

		$errors = $this->app['validator']->validateValue($location, new Assert\NotBlank([
			'message' => 'CANNOT_BE_BLANK'
		]));

		if (count($errors))
		{
			return new Response($this->app->trans($errors[0]->getMessage()), 400);
		}

		$this->app['db']->update('profiles', [
			'location' => $location
		], ['id' => $user['id']]);

		return true;
	}

	/**
	 * Saves the users location change
	 * @param  Request $request The request object
	 * @return bool|Response
	 */
	public function saveDob (Request $request)
	{
		$user = $this->app['session']->get('user');
		if (!$user)
		{
	        return new Response($this->app->trans('MUST_BE_LOGGED_IN'), 400);
		}

		$dob = $request->get('dob');

		$errors = $this->app['validator']->validateValue($dob, new Assert\NotBlank([
			'message' => 'CANNOT_BE_BLANK'
		]));

		if (count($errors))
		{
			return new Response($this->app->trans($errors[0]->getMessage()), 400);
		}

		$this->app['db']->update('profiles', [
			'dob' => $dob
		], ['id' => $user['id']]);

		return true;
	}

	public function findById ($id)
	{
		$id = (int) $id;
		if (!$id)
		{
			return false;
		}


		$cache_key = 'user-' . $id;
		$user = $this->app['cache']->get($cache_key, function () use ($id) {
			$data = array(
				'data' => $this->app['db']->fetchAssoc('SELECT id,username,ip,regdate,topics,posts,email,locale,perm_group FROM users WHERE id=? LIMIT 1', array(
					$id
				))
			);

			return $data;
		});

		if (!$user['data'])
		{
			return false;
		}

		$user['data']['profile'] = $this->getUserProfile($user['data']['id']);
		$user['data']['settings'] = $this->getUserSettings($user['data']['id']);
		$user['data']['group'] = $this->app['group']->findById($user['data']['perm_group']);

		return $user;
	}

	public function getUserSettings ($user_id)
	{
		$user_id = (int) $user_id;

		if (!$user_id)
		{
			return false;
		}

		$this->app['cache']->setCollection($this->app['database']['name'], 'settings');

		$settings = $this->app['cache']->get('settings-' . $user_id, function () use ($user_id) {
			$data = [
				'data' => $this->app['db']->fetchAssoc('SELECT * FROM settings WHERE id=? LIMIT 1', [
					$user_id
				])
			];

			return $data;
		});

		return $settings['data'];
	}

	public function getUserProfile ($id)
	{
		$id = (int) $id;

		if (!$id)
		{
			return false;
		}

		$this->app['cache']->setCollection($this->app['database']['name'], 'profiles');

		$profile = $this->app['cache']->get('profile-' . $id, function () use ($id) {
			$data = [
				 'data' => $this->app['db']->fetchAssoc('SELECT * FROM profiles WHERE id=? LIMIT 1', [
					$id
				])
			];

			return $data;
		});

		return $profile['data'];
	}

	public function findByUsername ($username)
	{
		if (!$username)
		{
			return false;
		}

		$this->app['cache']->setCollection($this->app['database']['name'], 'users');

		$cache_key = 'user-' . $username;
		$user = $this->app['cache']->get($cache_key, function () use ($username) {
			$data = array(
				'data' => $this->app['db']->fetchAssoc('SELECT id,username,ip,regdate,topics,posts,email,locale,perm_group FROM users WHERE username=? LIMIT 1', array(
					$username
				))
			);

			return $data;
		});

		if (!$user['data'])
		{
			return false;
		}

		$user['data']['profile'] = $this->getUserProfile($user['data']['id']);
		$user['data']['settings'] = $this->getUserSettings($user['data']['id']);
		$user['data']['group'] = $this->app['group']->findById($user['data']['perm_group']);

		return $user;
	}

	public function checkFollowing($user_id, $following_id)
	{
		$user_id = (int) $user_id;
		$following_id = (int) $following_id;

		if (!$user_id || !$following_id)
		{
			return false;
		}

		return $this->app['db']->fetchColumn('SELECT user_id FROM followers WHERE user_id=? AND following=? LIMIT 1', array(
			$user_id,
			$following_id
		));
	}

	public function follow (Request $request)
	{
		$response = new Response;
		$user_id = (int) $request->get('userId');

		if (!$user_id)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$user = $this->app['session']->get('user');

		if (!$user)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		$check = $this->app['db']->fetchAssoc('SELECT user_id FROM followers WHERE user_id=? AND following=? LIMIT 1', array(
			$user['id'],
			$user_id
		));

		if ($check)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('ALREADY_FOLLOWING'));
			return $response;
		}

		$this->app['db']->insert('followers', array(
			'user_id' => $user['id'],
			'following' => $user_id,
			'added' => time()
		));

		return true;
	}

	public function unfollow (Request $request)
	{
		$response = new Response;
		$user_id = (int) $request->get('userId');

		if (!$user_id)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$user = $this->app['session']->get('user');

		if (!$user)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		$check = $this->app['db']->fetchAssoc('SELECT user_id FROM followers WHERE user_id=? AND following=? LIMIT 1', array(
			$user['id'],
			$user_id
		));

		if (!$check)
		{
			$response->setStatusCode(403);
			$response->setContent($this->app->trans('NOT_FOLLOWING'));
			return $response;
		}

		$this->app['db']->delete('followers', array(
			'user_id' => $user['id'],
			'following' => $user_id
		));

		return true;
	}

	public function updateViews (Request $request) 
	{
		$user_id = (int) $request->get('userId');

		if (!$user_id)
		{
			return false;
		}

		$this->app['db']->executeQuery('UPDATE profiles SET views=views+1 WHERE id=? LIMIT 1', array(
			$user_id
		));

		return true;
	}

	public function findComments (Request $request)
	{
		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'profiles');

		$user_id = (int) $request->get('user_id');
		$page = (int) $request->get('page');

		if (!$user_id)
		{
			return false;
		}

		$cache_key = 'profile-comments-' . $user_id . '.' . $page;

		$comments['data'] = $this->app['cache']->get($cache_key, function () use ($user_id, $page) {
			$data = array(
				'data' => $this->app['db']->fetchAll('SELECT p.*, u.username FROM profile_comments p LEFT JOIN users u ON u.id=p.author WHERE p.profile=? AND p.deleted=0 ORDER BY p.added DESC LIMIT ' . (($page - 1)* 5) . ', 5', array(
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

		return json_encode($comments);
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
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		if (!$comment || strlen($comment) < 6)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('COMMENT_MIN_LENGTH', array(6)));
			return $response;
		}

		$time = time();

		if (!\ASF\Permissions::hasPermission('BYPASS_RESTRICTIONS'))
		{
			$lastAdded = $this->app['db']->fetchColumn('SELECT added FROM profile_comments WHERE profile=? AND author=? ORDER BY added DESC LIMIT 1', array(
				$profile_id,
				$user['id']
			));

			$time_since_last = $time - (int) $lastAdded;

			if ($time_since_last < 300)
			{
				$seconds = 300 - $time_since_last;
				$minutes = round($seconds / 60);
				$seconds = $seconds % 60;

				$response->setStatusCode(403);
				$response->setContent($this->app->trans('COMMENT_POST_LIMIT', array($minutes, $seconds)));
				return $response;
			}
		}

		$this->app['db']->insert('profile_comments', array(
			'profile' => $profile_id,
			'author' => $user['id'],
			'comment' => $comment,
			'added' => $time
		));

		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'profiles');
		
		$this->app['cache']->delete_group('profile-comments-' . $profile_id);

		return json_encode(array(
			'id'	=> $this->app['db']->lastInsertId(),
			'comment' => $comment,
			'added' => $time,
			'username' => $user['username'],
			'profileId' => $profile_id
		));
	}

	public function deleteComment (Request $request)
	{
		$comment_id = (int) $request->get('commentId');
		$response = new Response;

		if (!$comment_id)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$comment = $this->app['db']->fetchAssoc('SELECT * FROM profile_comments WHERE id=? LIMIT 1', array(
			$comment_id
		));

		if (!$comment)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		$user = $this->app['session']->get('user');

		if (!$user)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('MUST_BE_LOGGED_IN'));
			return $response;
		}

		if ($user['id'] != $comment['author'] && !\ASF\Permissions::hasPermission('EDIT_POSTS'))
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('NO_PERMISSION'));
			return $response;
		}

		$this->app['db']->update('profile_comments', array(
			'deleted' => 1
		), array('id' => $comment_id));

		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'profiles');

		$this->app['cache']->delete('profile-comment-' . $comment_id);
		$this->app['cache']->delete_group('profile-comments-' . $comment['profile']);

		return true;
	}

	public function likeComment (Request $request)
	{
		$comment_id = (int) $request->get('commentId');
		$username = $request->get('username');

		$response = new Response;

		if (!$comment_id)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		// Check if liked already
		$check = $this->app['db']->fetchColumn('SELECT username FROM profile_comment_likes WHERE username=? AND profile_comment=? LIMIT 1', array(
			$username,
			$comment_id
		));

		if ($check)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('ALREADY_LIKED'));
			return $response;
		}

		$this->app['db']->insert('profile_comment_likes', array(
			'profile_comment' => $comment_id,
			'username' => $username,
			'added' => time()
		));

		$this->app['cache']->collection = $this->app['cache']->setCollection($this->app['database']['name'], 'profiles');
		
		$cache_key = 'profile-comment-' . $comment_id . '-likes';
		$this->app['cache']->delete($cache_key);

		return true;
	}
}