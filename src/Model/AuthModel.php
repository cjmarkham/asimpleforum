<?php

namespace Model;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthModel 
{
	public $app;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
	}

	public function confirmEmail ($email, $user_id)
	{
		$response = new Response();
		$user_id = (int) $user_id;

		if (!$user_id || !$email)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$check = $this->app['db']->fetchAssoc('SELECT id, username, email, approved FROM users WHERE id=? AND email=? LIMIT 1', array(
			$user_id,
			$email
		));

		if (!$check)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('NO_USER'));
			return $response;
		}

		if ($check['approved'] == 1)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('ALREADY_CONFIRMED_EMAIL'));
			return $response;
		}

		$this->app['db']->update('users', array(
			'approved' => 1
		), array('id' => $user_id));


		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['database']['name'], 'users');
		$this->app['cache']->delete('user-' . $check['username']);

		$response->setStatusCode(200);
		$response->setContent($this->app->trans('EMAIL_CONFIRMED'));
		return $response;
	}

	public function signup (array $data)
	{
		$response = new Response;

		if (empty($data))
		{
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return false;
		}

		$constraints = new Assert\Collection(array(
			'username' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Length(array('min' => 4)),
				new Assert\Length(array('max' => 15))
			),
			'password' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Length(array('min' => 4))
			),
			'confirm' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Length(array('min' => 4))
			),
			'email' => array(
				new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
				)),
				new Assert\Email(array(
					'message' => 'INVALID_EMAIL'
				))
			),
			'terms' => array(new Assert\NotBlank(array(
					'message' => 'FILL_ALL_FIELDS'
			)))
		));

		$errors = $this->app['validator']->validateValue($data, $constraints);

		if (count($errors) > 0)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans($errors[0]->getMessage()));
			return $response;
		}

		if ($data['password'] !== $data['confirm'])
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('PASSWORDS_DONT_MATCH'));
			return $response;
		}

		$check_username = $this->app['db']->fetchColumn('SELECT username FROM users WHERE username=? LIMIT 1', array(
			$data['username']
		));

		if ($check_username)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('USERNAME_TAKEN'));
			return $response;
		}

		$check_email = $this->app['db']->fetchColumn('SELECT email FROM users WHERE email=? LIMIT 1', array(
			$data['email']
		));

		if ($check_email)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app->trans('EMAIL_REGISTERED'));
			return $response;
		}

		$hashed = $this->hash($this->app['defaults']['salt'] . $data['password']);

		$default_group = $this->app['db']->fetchColumn('SELECT id FROM groups WHERE `default`=1 LIMIT 1');

		$insert = $this->app['db']->insert('users', array(
			'username' => $data['username'],
			'password' => $hashed,
			'email'    => $data['email'],
			'ip'       => $_SERVER['REMOTE_ADDR'],
			'perm_group'=> $default_group,
			'regdate'  => time(),
			'lastActive' => time()
		));

		if (!$insert)
		{
			$response->setStatusCode(500);
			$response->setContent($this->app->trans('UNKNOWN_ERROR'));
			return $response;
		}

		$user_id = $this->app['db']->lastInsertId();

		$this->app['db']->insert('profiles', array(
			'id' => $user_id
		));

		if ($this->app['board']['confirmEmail'])
		{
			\ASF\Mailer::setTemplate('emailConfirmation', array(
				'username' => $data['username'],
				'boardTitle' => $this->app['board']['name'],
				'boardUrl'   => $this->app['board']['url'],
				'confirmCode' => base64_encode($data['email'] . '-' . $user_id)
			));

			\ASF\Mailer::send($data['email'], $this->app['email']['noReply'], 'Email confirmation');
			
			$response->setStatusCode(200);
			$response->setContent($this->app->trans('ACCOUNT_CREATED_CONFIRM_EMAIL'));
		}
		else
		{
			$response->setStatusCode(200);
			$resopnse->setContent($this->app->trans('ACCOUNT_CREATED'));
		}

		return $response;
	}

	public function hash ($password)
	{
		return sha1(md5(sha1($password)));
	}

	public function login (array $data)
	{
		$username = $data['username'];
		$password = $data['password'];

		$user = $this->app['db']->fetchAssoc('SELECT * FROM users WHERE username=?', array(
			$username
		));

		if (!$user)
		{
			return new Response($this->app->trans('NO_USER'), 400);
		}

		if ($user['password'] !== $this->hash($this->app['defaults']['salt'] . $password))
		{
			return new Response($this->app->trans('INVALID_CREDENTIALS'), 400);
		}

		if (!$user['approved'] && $this->app['board']['confirmEmail'])
		{
			return new Response($this->app->trans('NOT_APPROVED'), 400);
		}

		$user['notifications'] = ['read' => [], 'unread' => []];

		$notifications = $this->app['notification']->findByUser($user['id']);

		foreach ($notifications as $notification)
		{
			if ($notification['read'])
			{
				$user['notifications']['read'][] = $notification;
			}
			else
			{
				$user['notifications']['unread'][] = $notification;
			}
		}

		$user['group'] = $this->app['group']->findById($user['perm_group']);

		$this->app['session']->set('userId', $user['id']);
		$this->app['session']->set('user', $user);

		return new Response(json_encode($user), 200);
	}
}