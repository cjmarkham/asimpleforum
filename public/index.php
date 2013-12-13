<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['config'] = new Config;

if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
{
    $app['debug'] = true;
    $app['config']->base = 'development';
}
else if (strpos($_SERVER['HTTP_HOST'], 'staging') !== false)
{
    $app['debug'] = false;
    $app['config']->base = 'staging';
}
else
{
    $app['debug'] = false;
    $app['config']->base = 'production';
}

$app['config']->load();

$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\SessionServiceProvider(), array(
    'session.storage.options' => array(
        'name' => $app['config']->cookie['name'],
        'cookie_domain' => $app['config']->cookie['domain']
    )
));

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/src/View'
));

$app->register(new Silex\Provider\ValidatorServiceProvider());

$twig = $app['twig'];
$twig->addExtension(new \Entea\Twig\Extension\AssetExtension(
    $app
));

$bbcode = new Twig_SimpleFilter('bbcode', array('Utils', 'bbcode'));
$truncate = new Twig_SimpleFunction('truncate', array('Utils', 'truncate'));

$app['twig']->addFilter($bbcode);
$app['twig']->addFunction($truncate);

/*$default_cache = $app['config']->defaults['cache'];

if ($default_cache === 'disk')
{
    $app->register(new \DiskCache\DiskCacheServiceProvider(), array(
        'diskcache.cache_dir' => dirname(__DIR__) . '/cache'
    ));

    $app['cache'] = $app['diskcache'];
} 
else
{
    $app->register(new Rafal\MemcacheServiceProvider\MemcacheServiceProvider());

    $app['cache'] = $app['memcache'];
}*/

/*$app->register(new Mongo\Silex\Provider\MongoServiceProvider, array(
    'mongo.connections' => array(
        'default' => array(
            'server' => 'mongodb://' . $app['config']->database['host'] . ':' . $app['config']->database['port'],
            'options' => array("connect" => true)
        )
    ),
));*/

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'dbname'   => $app['config']->database['name'],
        'host'      => $app['config']->database['host'],
        'user'      => $app['config']->database['user'],
        'password'  => $app['config']->database['password'],
    ),
));

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
$app->register(new Tobiassjosten\Silex\Provider\FacebookServiceProvider(), array(
    'facebook.app_id'     => '480210532061315',
    'facebook.secret'     => 'f5bc907e9ac2bb6ea651fc9bfe89f7b8',
));

$app->error(function (\Exception $e, $code) use ($app) {

    if ($code === 404)
    {

        return new Response(
            $app['twig']->render(
                '404.twig', 
                array(
                    'title'          => 'You lost or something?', 
                    'debug'          => $app['debug'],
                    'message'        => $e->getMessage()
                )
            ), 
        404);
    }

});

Route::$app = $app;
Message::$app = $app;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Models
$app['forum'] = $app->share(function() use ($app) {
    $model = new \Model\ForumModel($app);
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


$app['auth'] = $app->share(function() use ($app) {
    $model = new \Model\AuthModel($app);
    return $model;
});

$app['language'] = $app->share(function() use ($app) {
    $model = new Language($app);
    return $model;
});

/*$app['modelName'] = $app->share(function() use ($app) {
    $model = new \Model\ModelName($app);
    return $model;
});*/

// Routes
$app->get('/', function (Application $app) {
    return Route::get('home:index');
});

$app->get('/signup', function (Application $app) {
    return Route::get('auth:signup');
});

$app->post('/signup', function (Request $request) {
    return Route::get('auth:signup', $request);
});

$app->post('/login', function (Request $request) use ($app) {
    return $app['auth']->login($request);
});

$app->get('/logout', function (Application $app) {
    return Route::get('auth:logout');
});

$app->post('/partial/{name}', function (Request $request, $name) use ($app) {

    $params = $request->get('params');
    $array = array();

    foreach ($params as $key => $param)
    {
        $array[$key] = $param;
    }

    $array['user'] = $app['session']->get('user');

    return $app['twig']->render('Partials/' . $name . '.twig', $array);
});

$app->get('/partial/{name}', function (Application $app, $name) {
    return $app['twig']->render('Partials/' . $name . '.twig', array(
        'user' => $app['session']->get('user')
    ));
});

$app->get('/{name}-{id}/{page}', function (Application $app, $name, $id, $page) {
    return Route::get('forum:index', $name, $id, $page);
})->assert('page', '([0-9]+)');

$app->get('/{name}-{id}', function (Application $app, $name, $id) {
    return Route::get('forum:index', $name, $id);
});

$app->get('/{forum_name}/{topic_name}-{topic_id}/{page}', function (Application $app, $topic_name, $topic_id, $page) {
    return Route::get('topic:index', $topic_name, $topic_id, $page);
})->assert('page', '([0-9]+)');

$app->get('/{forum_name}/{topic_name}-{topic_id}', function (Application $app, $topic_name, $topic_id) {
    return Route::get('topic:index', $topic_name, $topic_id);
});

$app->post('/topic/{method}', function (Request $request, $method) use ($app) {
    if (!method_exists($app['topic'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['topic']->$method($request);
});

$app->post('/post/{method}', function (Request $request, $method) use ($app) {
    if (!method_exists($app['post'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['post']->$method($request);
});

/*$app->post('/game/update_plays', function (Request $request) {
    return Route::get('game:update_plays', $request);
});*/

if (strpos($_SERVER['REQUEST_URI'], '?purge') !== false)
{
    $app['cache']->flush();
}

//$app['auth']->load();

$app->run();