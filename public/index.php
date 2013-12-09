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

$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\SessionServiceProvider());

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(dirname(__FILE__)) . '/src/View'
));

$app->register(new Silex\Provider\ValidatorServiceProvider());

$twig = $app['twig'];
$twig->addExtension(new \Entea\Twig\Extension\AssetExtension(
    $app
));

$app['config']->load('defaults');
$default_cache = $app['config']->get('defaults.cache');

if ($default_cache === 'disk')
{
    $app->register(new \DiskCache\DiskCacheServiceProvider(), array(
        'cache.cache_dir' => dirname(dirname(__FILE__)) . '/cache'
    ));
} 
else
{
    $app->register(new Rafal\MemcacheServiceProvider\MemcacheServiceProvider());
}

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

// Doctrine
$app['config']->load('database');
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' 	=> 'pdo_mysql',
        'host' 		=> $app['config']->get('database.host'),
        'user' 		=> $app['config']->get('database.user'),
        'password' 	=> $app['config']->get('database.password'),
        'dbname' 	=> $app['config']->get('database.name'),
        'charset' 	=> 'utf8'
    )
));

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

$app['auth'] = $app->share(function() use ($app) {
    $model = new \Model\AuthModel($app);
    return $model;
});

$app['language'] = $app->share(function() use ($app) {
    $model = new Language($app);
    return $model;
});

$app['config']->load('cookie');

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

$app->get('/partial/{name}', function (Application $app, $name) {
    return $app['twig']->render('Partials/' . $name . '.twig', array(
        'user' => $app['session']->get('user')
    ));
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