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
    $app['local'] = true;
    $app['config']->base = 'development';
}
else if (strpos($_SERVER['HTTP_HOST'], 'staging') !== false)
{
    $app['debug'] = true;
    $app['local'] = false;
    $app['config']->base = 'staging';
}
else
{
    $app['debug'] = false;
    $app['local'] = false;
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

$app['twig']->addExtension(new \Entea\Twig\Extension\AssetExtension(
    $app
));

$truncate = new Twig_SimpleFunction('truncate', array('Utils', 'truncate'));
$config_function = new Twig_SimpleFunction('config', function ($file, $key = false) use ($app) {
    if (property_exists($app['config'], $file))
    {
        if ($key)
        {
            return $app['config']->{$file}[$key];
        }
        else
        {
            return $app['config']->{$file};
        }
    } 
});

$permissions_function = new Twig_SimpleFunction('hasPermission', function ($action) use ($app) {
    return Permissions::hasPermission($action);
});

$app['twig']->addFunction($truncate);
$app['twig']->addFunction($config_function);
$app['twig']->addFunction($permissions_function);

$app->register(new Mongo\Silex\Provider\MongoServiceProvider, array(
    'mongo.connections' => array(
        'default' => array(
            'server' => 'mongodb://' . $app['config']->database['host'] . ':' . $app['config']->database['port'],
            'options' => array("connect" => true)
        )
    )
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'dbname'   => $app['config']->database['name'],
        'host'      => $app['config']->database['host'],
        'user'      => $app['config']->database['user'],
        'password'  => $app['config']->database['password'],
    ),
));

if ($app['config']->defaults['cache'] === 'disk')
{
    $app->register(new DiskCache\DiskCacheServiceProvider(), array(
        'diskcache.cache_dir' => dirname(__DIR__) . '/cache'
    ));

    $app['cache'] = $app['diskcache'];
} 
else
{
    $app->register(new MongoCache\MongoCacheServiceProvider());
    $app['cache'] = $app['mongocache'];
}

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
    else
    {
        if ($app['debug'])
        {
            die(var_dump($e->getMessage()));
        }
    }

});

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

    $app['whoops'] = $app->extend('whoops', function($whoops) {
        $whoops->pushHandler(new DeleteWholeProjectHandler);
        return $whoops;
    });
}

if (strpos($_SERVER['REQUEST_URI'], '?purge') !== false)
{
    $app['cache']->flush($app, 'default', 'asf_forum');
}

$app->before(function (Request $request) use ($app) {
    $user = $app['session']->get('user');

    if (!empty($user) && $user['approved'] == 0) 
    {
        \Message::alert('LOGGED_IN_NOT_APPROVED');
    }

    $app['sessions']->update();
    $sessions = $app['sessions']->get();

    $recent_topics = $app['topic']->find_recent(4);

    $config = array(
        'default' => $app['config']->defaults,          
        'board' => $app['config']->board            
    );

    $app['twig']->addGlobal('user', $user);
    $app['twig']->addGlobal('config', $config);
    $app['twig']->addGlobal('recent_topics', $recent_topics);
    $app['twig']->addGlobal('sessions', $sessions);

});

$app->finish(function (Request $request) use ($app, $logger) {
    
    if (!$request->isXMLHttpRequest())
    {
        $time = 0;
        if (count($logger->queries))
        {
            foreach ($logger->queries as $query)
            {
               $time += $query['executionMS'];
               $sql = preg_replace('/([A-Z]{2,})/', '<strong>$1</strong>', $query['sql']);
               $queries[] = preg_replace('/\?/', '<strong style="color:red">?</strong>', $sql);
            }

            $time = round($time, 4);

            $query_length = count($logger->queries);
            $query_list = implode('<br /><hr />', $queries);

/*echo <<<HEREDOC
    <div id="logger">
        <button onclick="$(this).next().slideToggle()" class="btn btn-danger">
            Queries: $query_length
        </button>
        <div id="query-list">
            $query_list
        </div>
        <button class="btn btn-warn">
            Query Time: $time
        </button>
    </div>
HEREDOC;*/
        }
    }

}, Application::LATE_EVENT);

$app->run();