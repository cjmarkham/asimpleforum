<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app['env'] = getenv('APP_ENV') ?: 'production';

$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

$root = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
$root = $root[0];

if (!file_exists(__DIR__ . '/../config/' . $app['env'] . '.json'))
{
    header('Location: /' . $root . '/install/');
    exit;
}

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $app['env'] . '.json'));


$app->register(new \Silex\Provider\SessionServiceProvider(), array(
    'session.storage.options' => array(
        'name' => $app['cookie']['name'],
        'cookie_domain' => $app['cookie']['domain']
    )
));

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/src/View'
));

$app->register(new Silex\Provider\ValidatorServiceProvider());

$truncate = new Twig_SimpleFunction('truncate', array('Utils', 'truncate'));
$config_function = new Twig_SimpleFunction('config', function ($section, $key = false) use ($app) {
    if (isset($app[$section]))
    {
        if (is_array($app[$section]))
        {
            if (isset($app[$section][$key]))
            {
                return $app[$section][$key];
            }

            return false;
        }
        return $app[$section];
    }
});

$permissions_function = new Twig_SimpleFunction('hasPermission', function ($action) use ($app) {
    return Permissions::hasPermission($action);
});

$repeat_function = new Twig_SimpleFunction('repeat', function ($string, $length) {
    return str_repeat($string, $length);
});

$app['twig']->addFunction($truncate);
$app['twig']->addFunction($config_function);
$app['twig']->addFunction($permissions_function);
$app['twig']->addFunction($repeat_function);

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'dbname'   => $app['database']['name'],
        'host'      => $app['database']['host'],
        'user'      => $app['database']['user'],
        'password'  => $app['database']['password']
    )
));

if ($app['defaults']['cache'] === 'disk')
{
    $app->register(new DiskCache\DiskCacheServiceProvider(), array(
        'diskcache.cache_dir' => dirname(__DIR__) . '/cache'
    ));

    $app['cache'] = $app['diskcache'];
} 
else
{
    $app->register(new Mongo\Silex\Provider\MongoServiceProvider, array(
        'mongo.connections' => array(
            $app['database']['name'] => array(
                'server' => 'mongodb://' . $app['mongo']['host'] . ':' . $app['mongo']['port'],
                'options' => array("connect" => true)
            )
        )
    ));
        
    $app->register(new MongoCache\MongoCacheServiceProvider());
    $app['cache'] = $app['mongocache'];
}

$app['cache']->app = $app;

$logger = new Doctrine\DBAL\Logging\DebugStack();
$app['db']->getConfiguration()->setSQLLogger($logger);

$app->register(new \Silex\Provider\ServiceControllerServiceProvider());

/*if ($app['debug'])
{
    $app->register(new \Silex\Provider\WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => __DIR__.'/../cache/profiler',
        'profiler.mount_prefix' => '/_profiler', // this is the default
    ));

    $webProfilerPath = dirname(dirname(__FILE__)) . '/vendor/symfony/web-profiler-bundle/Symfony/Bundle/WebProfilerBundle/Resources/views'; 
    $app['twig.loader.filesystem']->addPath($webProfilerPath, 'WebProfiler');
}*/

// Facebook SDK
/*$app->register(new Tobiassjosten\Silex\Provider\FacebookServiceProvider(), array(
    'facebook.app_id'     => '480210532061315',
    'facebook.secret'     => 'f5bc907e9ac2bb6ea651fc9bfe89f7b8',
));*/

Route::$app = $app;
Message::$app = $app;
Permissions::$app = $app;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Models
$app['sessions'] = $app->share(function() use ($app) {
    $model = new \Model\SessionModel($app);
    return $model;
});

$app['forum'] = $app->share(function() use ($app) {
    $model = new \Model\ForumModel($app);
    return $model;
});

$app['alert'] = $app->share(function() use ($app) {
    $model = new \Model\AlertModel($app);
    return $model;
});

$app['search'] = $app->share(function() use ($app) {
    $model = new \Model\SearchModel($app);
    return $model;
});

$app['group'] = $app->share(function() use ($app) {
    $model = new \Model\GroupModel($app);
    return $model;
});

$app['topic'] = $app->share(function() use ($app) {
    $model = new \Model\TopicModel($app);
    return $model;
});

$app['post'] = $app->share(function() use ($app) {
    $model = new \Model\PostModel($app);
    return $model;
});

$app['user'] = $app->share(function() use ($app) {
    $model = new \Model\UserModel($app);
    return $model;
});

$app['auth'] = $app->share(function() use ($app) {
    $model = new \Model\AuthModel($app);
    return $model;
});

$app['language'] = $app->share(function() use ($app) {
    $model = new Language($app);
    return $model;
});

// Routes
include 'routes.php';

if ($app['debug'] === true) 
{
    $app->register(new Whoops\Provider\Silex\WhoopsServiceProvider);
}

if (strpos($_SERVER['REQUEST_URI'], '?purge') !== false)
{
    $app['cache']->flush($app, 'default', 'asf_forum');
}