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
			\Message::error('UNKNOWN_ERROR');
			return false;
		}

		$check = $this->app['db']->fetchAssoc('SELECT id, username, email, approved FROM users WHERE id=? AND email=? LIMIT 1', array(
			$user_id,
			$email
		));

		if (!$check)
		{
			\Message::error('NO_USER');
			return false;
		}

		if ($check['approved'] == 1)
		{
			\Message::error('ALREADY_CONFIRMED_EMAIL');
			return false;
		}

		$this->app['db']->update('users', array(
			'approved' => 1
		), array('id' => $user_id));

		$this->app['cache']->collection = $this->app['mongo']['default']->selectCollection($this->app['config']->database['name'], 'users');
		$this->app['cache']->delete('user-' . $check['username']);

		\Message::alert('EMAIL_CONFIRMED');
		return true;
	}

	public function signup (array $data)
	{
		if (empty($data))
		{
			\Message::error('UNKNOWN_ERROR');
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
			\Message::error($errors[0]->getMessage());
			return false;
		}

		if ($data['password'] !== $data['confirm'])
		{
			\Message::error('PASSWORDS_DONT_MATCH');
			return false;
		}

		$check_username = $this->app['db']->fetchColumn('SELECT username FROM users WHERE username=? LIMIT 1', array(
			$data['username']
		));

		if ($check_username)
		{
			\Message::error('USERNAME_TAKEN');
			return false;
		}

		$check_email = $this->app['db']->fetchColumn('SELECT email FROM users WHERE email=? LIMIT 1', array(
			$data['email']
		));

		if ($check_email)
		{
			\Message::error('EMAIL_REGISTERED');
			return false;
		}

		$hashed = $this->hash($this->app['config']->defaults['salt'] . $data['password']);

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
			\Message::error('UNKNOWN_ERROR');
			return false;
		}

		$insert = $this->app['db']->insert('profiles', array(
			'id' => $this->app['db']->lastInsertId()
		));

		if ($this->app['config']->board['confirmEmail'])
		{
			\Mailer::setTemplate('emailConfirmation', array(
				'username' => $data['username'],
				'boardTitle' => $this->app['config']->board['name'],
				'boardUrl'   => $this->app['config']->board['url'],
				'confirmCode' => base64_encode($data['email'] . '- ' . $this->app['db']->lastInsertId())
			));

			\Mailer::send($data['email'], $this->app['config']->email['noReply'], 'Email confirmation');
		
			\Message::alert('Your account has been created but you will need to confirm your email address before logging in. Check your emails for details on how to do so.');
		}
		else
		{
			\Message::alert('Your account has been created and you can now log in.');
		}

		return true;
	}

	public function hash ($password)
	{
		return sha1(md5(sha1($password)));
	}

	public function login (Request $request)
	{
		$response = new Response();

		$username = $request->get('username');
		$password = $request->get('password');

		$user = $this->app['db']->fetchAssoc('SELECT * FROM users WHERE username=?', array(
			$username
		));

		if (!$user)
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('NO_USER'));
			return $response;
		}

		if ($user['password'] !== $this->hash($this->app['config']->defaults['salt'] . $password))
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('INVALID_CREDENTIALS'));
			return $response;
		}

		if (!$user['approved'])
		{
			$response->setStatusCode(400);
			$response->setContent($this->app['language']->phrase('NOT_APPROVED'));
			return $response;
		}

		$user['group'] = $this->app['group']->find_by_id($user['perm_group']);

		$this->app['session']->set('user', $user);

		return new Response(json_encode($this->app['session']->get('user')), 200);
	}
}