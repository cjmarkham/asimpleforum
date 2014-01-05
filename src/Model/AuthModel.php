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

	public function signup (array $data)
	{
		if (empty($data))
		{
			\Message::error('UNKNOWN_ERROR');
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
			'group'	   => $default_group,
			'regdate'  => time(),
			'lastActive' => time()
		));

		if (!$insert)
		{
			\Message::error('UNKNOWN_ERROR');
			return false;
		}

		if ($this->app['config']->board['confirmEmail'] === true)
		{
			\Mailer::setTemplate('emailConfirmation', array(
				'username' => $data['username'],
				'boardTitle' => $this->app['config']->board['name'],
				'boardUrl'   => $this->app['config']->board['url'],
				'confirmCode' => base64_encode($data['email'] . '- ' . $this->app['db']->lastInsertId())
			));

			\Mailer::send($data['email'], $this->app['config']->email['noReply'], 'Email confirmation');
		}

		// Todo return success message
		return true;
	}

	public function hash ($password)
	{
		return sha1(md5(sha1($password)));
	}

	public function login (Request $request)
	{
		$username = $request->get('username');
		$password = $request->get('password');

		$user = $this->app['db']->fetchAssoc('SELECT * FROM users WHERE username=?', array(
			$username
		));

		if (!$user)
		{
			$response = new Response();
			$response->setStatusCode(400);
			$response->setContent('NO_USER');
			return $response;
		}

		if ($user['password'] !== $this->hash($this->app['config']->defaults['salt'] . $password))
		{
			$response = new Response();
			$response->setStatusCode(400);
			$response->setContent('INVALID_CREDENTIALS');
			return $response;
		}

		$user['group'] = $this->app['group']->find_by_id($user['group']);

		$this->app['session']->set('user', $user);

		return new Response(json_encode($this->app['session']->get('user')), 200);
	}
}