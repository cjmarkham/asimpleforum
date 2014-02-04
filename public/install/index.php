<?php

error_reporting(0);

$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoload))
{
	exit('Please run "composer install" before attempting to install.');
}

$root = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
$root = $root[0];

require $autoload;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();
$app['env'] = getenv('APP_ENV') ?: 'production';

$controller = new InstallController;
$controller->app = $app;
$controller->root = $root;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__
));

$app->get('/forum/install/{method}/', function (Application $app, $method) use ($controller) {
	if (!method_exists($controller, $method))
	{
		return false;
	}

	return $controller->$method();
});

$app->post('/forum/install/{method}/', function (Request $request, $method) use ($controller) {
	if (!method_exists($controller, $method))
	{
		return false;
	}

	return $controller->$method($request);
});

$app->get('/forum/install/', function (Application $app) use ($controller) {
	return $controller->index();
});

$app['debug'] = true;

$app->register(new \Silex\Provider\SessionServiceProvider());

$app->run();

class InstallController
{
	public $app;
	public $root;

	private $settings = array();

	public function index ()
	{
		$php_version = phpversion();
		$mysql_support = extension_loaded('mysql');
		$mysqli_support = extension_loaded('mysqli');
		$pdo_support = extension_loaded('pdo');
		$mongo_support = extension_loaded('mongo');

		return $this->app['twig']->render('index.twig', array(
			'php_version' => $php_version,
			'mysql_support' => $mysql_support,
			'mysqli_support' => $mysqli_support,
			'pdo_support' => $pdo_support,
			'mongo_support' => $mongo_support,
		));
	}

	public function checkDBConnection (Request $request)
	{
		$response = new Response;

		$dbhost = $request->get('dbhost');
		$dbuser = $request->get('dbuser');
		$dbpass = $request->get('dbpass');
		$dbname = $request->get('dbname');

		if (!$dbhost || !$dbuser || !$dbpass || !$dbname)
		{
			$response->setStatusCode(400);
			$response->setContent('Please fill in all fields.');

			return $response;
		}

		$this->app['session']->set('db-host', $dbhost);
		$this->app['session']->set('db-user', $dbuser);
		$this->app['session']->set('db-pass', $dbpass);
		$this->app['session']->set('db-name', $dbname);

		$mongohost = $request->get('mongohost');
		$mongoport = $request->get('mongoport');

		if ($mongohost && $mongoport)
		{
			$this->app['session']->set('mongo-host', $mongohost);
			$this->app['session']->set('mongo-port', $mongoport);

			try
			{
				$this->app->register(new Mongo\Silex\Provider\MongoServiceProvider, array(
				    'mongo.connections' => array(
				        $dbname => array(
				            'server' => 'mongodb://' . $mongohost . ':' . $mongoport,
				            'options' => array("connect" => true)
				        )
				    )
				));

				$connection = $this->app['mongo'][$dbname]->connected;

				if (!$connection)
				{
					$response->setStatusCode(400);
					$response->setContent('Could not connect to MongoDB');

					return $response;
				}

				$db = $this->app['mongo'][$dbname]->selectDB($this->app['session']->get('db-name'));
				$db->listCollections();
			} 
			catch (\Exception $e)
			{
				$response->setStatusCode(400);
				$response->setContent('Could not connect to MongoDB');

				return $response;
			}

		}

		$this->app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		    'db.options' => array(
		        'driver'    => 'pdo_mysql',
		        'dbname'    => $dbname,
		        'host'      => $dbhost,
		        'user'      => $dbuser,
		        'password'  => $dbpass
		    )
		));

		try 
		{
			$this->app['db']->executeQuery('SET NAMES utf8');

			$response->setStatusCode(200);
			$response->setContent('Successfully established a database connection.');
		}
		catch (\Exception $e)
		{
			$response->setStatusCode(400);
			$response->setContent('Could not connect to database. Has it been created yet?');
		}

		return $response;
	}

	public function addAdminUser (Request $request)
	{
		$this->app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		    'db.options' => array(
		        'driver'    => 'pdo_mysql',
		        'dbname'    => $this->app['session']->get('db-name'),
		        'host'      => $this->app['session']->get('db-host'),
		        'user'      => $this->app['session']->get('db-user'),
		        'password'  => $this->app['session']->get('db-pass')
		    )
		));

		$response = new Response;

		$username = $request->get('username');
		$password = $request->get('password');
		$confirm  = $request->get('confirm');
		$email    = $request->get('email');

		$salt = $request->get('salt');

		if (!$username || !$password || !$confirm || !$email || !$salt)
		{
			$response->setStatusCode(400);
			$response->setContent('Please fill in all fields.');

			return $response;
		}

		if ($password !== $confirm)
		{
			$response->setStatusCode(400);
			$response->setContent('Your chosen passwords do not match.');

			return $response;
		}

		$hashed = sha1(md5(sha1($salt . $password)));

		$this->app['session']->set('admin-username', $username);
		$this->app['session']->set('admin-hashed', $hashed);
		$this->app['session']->set('admin-email', $email);

		$this->app['session']->set('salt', $salt);

		$response->setStatusCode(200);

		return $response;
	}

	public function createDatabase ()
	{
		$this->app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		    'db.options' => array(
		        'driver'    => 'pdo_mysql',
		        'dbname'    => $this->app['session']->get('db-name'),
		        'host'      => $this->app['session']->get('db-host'),
		        'user'      => $this->app['session']->get('db-user'),
		        'password'  => $this->app['session']->get('db-pass')
		    )
		));

		if ($this->app['session']->get('mongo-host'))
		{
			$this->app->register(new Mongo\Silex\Provider\MongoServiceProvider, array(
			    'mongo.connections' => array(
			        'default' => array(
			            'server' => 'mongodb://' . $this->app['session']->get('mongo-host') . ':' . $this->app['session']->get('mongo-port'),
			            'options' => array("connect" => true)
			        )
			    )
			));
		}

		foreach (glob('sql/*.sql') as $sql_file)
		{
			$this->app['db']->executeQuery(file_get_contents($sql_file));
		}

		return true;
	}

	public function populateDatabase ()
	{
		$this->app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		    'db.options' => array(
		        'driver'    => 'pdo_mysql',
		        'dbname'    => $this->app['session']->get('db-name'),
		        'host'      => $this->app['session']->get('db-host'),
		        'user'      => $this->app['session']->get('db-user'),
		        'password'  => $this->app['session']->get('db-pass')
		    )
		));

		$response = new Response;

		$this->app['db']->insert('users', array(
			'username' => $this->app['session']->get('admin-username'),
			'password' => $this->app['session']->get('admin-hashed'),
			'email' => $this->app['session']->get('admin-email'),
			'ip' => $_SERVER['REMOTE_ADDR'],
			'perm_group'=> 1,
			'regdate' => time(),
			'lastActive' => time(),
			'approved' => 1
		));

		$id = $this->app['db']->lastInsertId();

		if (!$id)
		{
			$response->setStatusCode(500);
			$response->setContent('An error has occurred while creating the admin user.');

			return $response;
		}

		$this->app['db']->insert('profiles', array(
			'id' => $id
		));

		return true;
	}

	public function createConfigFile ()
	{
		$config_dir = __DIR__ . '/../../config';

		$dist_file = $this->app['env'] . '.dist.json';

		$json = json_decode(file_get_contents($dist_file), true);

		$json['database']['host'] = $this->app['session']->get('db-host');
		$json['database']['user'] = $this->app['session']->get('db-user');
		$json['database']['password'] = $this->app['session']->get('db-pass');
		$json['database']['name'] = $this->app['session']->get('db-name');

		if ($this->app['session']->get('mongo-host'))
		{
			$json['defaults']['cache'] = 'mongo';
			$json['mongo']['host'] = $this->app['session']->get('mongo-host');
			$json['mongo']['port'] = $this->app['session']->get('mongo-port');
		}
		else
		{
			$json['defaults']['cache'] = 'disk';
		}

		$json['defaults']['salt'] = $this->app['session']->get('salt');

		$json['emails']['noReply'] = $this->app['session']->get('admin-email');

		if (!is_dir($config_dir))
		{
			mkdir($config_dir, 0777);
			// For windows
			chmod(0777, $config_dir);
		}

		$json['cookie']['name'] = 'asimpleforum';
		$json['cookie']['path'] = '/';
		$json['cookie']['domain'] = $_SERVER['HTTP_HOST'];

		$json['board']['base'] = $this->root . '/';

		$json = json_encode($json);

		file_put_contents($config_dir . '/' . $this->app['env'] . '.json', $json);
		return true;
	}

	public function saveSettings (Request $request)
	{
		$config_dir = __DIR__ . '/../../config';
		$file = $config_dir . '/' . $this->app['env'] . '.json';

		$json = json_decode(file_get_contents($file), true);

		$board_name = $request->get('board_name');
		$board_logo = $request->get('board_logo');
		$board_url = $request->get('board_url');
		$posts_per_page = (int) $request->get('posts_per_page');
		$topics_per_page = (int) $request->get('topics_per_page');
		$confirm_email = $request->get('confirm_email');
		$double_post = $request->get('double_post');
		$cookie_name = $request->get('cookie_name');
		$cookie_path = $request->get('cookie_path');
		$cookie_domain = $request->get('cookie_domain');

		if (!$posts_per_page)
		{
			$posts_per_page = 10;
		}

		if (!$topics_per_page)
		{
			$topics_per_page = 10;
		}

		$json['board']['name'] = $board_name;
		$json['board']['logo'] = $board_logo;
		$json['board']['url'] = $board_url;
		$json['board']['postsPerPage'] = $posts_per_page;
		$json['board']['topicsPerPage'] = $topics_per_page;
		$json['board']['confirmEmail'] = $confirm_email == 1 ? true : false;

		$json = json_encode($json);

		file_put_contents($config_dir . '/' . $this->app['env'] . '.json', $json);
		return true;
	}
}