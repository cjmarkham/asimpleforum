<?php

date_default_timezone_set("Europe/London");

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\PhpFileLoader;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * An extension to Silex\Application
 */
class ASFApplication extends Silex\Application
{
    use \ASF\LanguageTrait;
    use Silex\Application\TranslationTrait;
}

$app = new ASFApplication();

// Set the environment
$app['env'] = getenv('APP_ENV') ?: 'production';

$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en')
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('php', new PhpFileLoader());

    $translator->addResource('php', __DIR__ . '/Locales/en.php', 'en');
    $translator->addResource('php', __DIR__ . '/Locales/fr.php', 'fr');

    return $translator;
}));

// Get the base directory for the forum
$root = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
$root = $root[0] . '/';

// If there isnt a config file then the forum needs to be installed
if (!file_exists(__DIR__ . '/../config/' . $app['env'] . '.json'))
{
    header('Location: /' . $root . 'install/');
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

$compression_method = $app['compression'];

if (in_array($compression_method, array('yui', 'closure')))
{
    if (!isset($app['java_path']))
    {
        throw new Exception('A path to Java needs to be specified to use YUI or Google Closure compression methods.');
    }
}

/**
 * Set compression method based on config
 */
switch ($compression_method)
{
    case 'yui':
        $css_filter = 'Yui\CssCompressorFilter';
        $js_filter  = 'Yui\JsCompressorFilter';
        break;
    case 'closure':
        $css_filter = 'GoogleClosure\CompilerJarFilter';
        $js_filter  = 'GoogleClosure\CompilerJarFilter';
        break;
    case 'min':
    default:
        $css_filter = 'CssMinFilter';
        $js_filter  = 'JsMinFilter';
        break;
}

$js_filter = 'Assetic\Filter\\' . $js_filter;
$css_filter = 'Assetic\Filter\\' . $css_filter;

$app->register(new SilexAssetic\AsseticServiceProvider(), array(
    'assetic.options' => array(
        'debug' => $app['debug'],
        'auto_dump_assets' => false
    ),
    'assetic.path_to_web' => __DIR__ . '/../public/'
   
));

$app['assetic.filter_manager'] = $app->share(
    
    $app->extend('assetic.filter_manager', function($fm, $app) use ($css_filter, $js_filter) {
        $fm->set('css_filter', new $css_filter($app['jar_path'], $app['java_path']));
        $fm->set('js_filter', new $js_filter($app['jar_path'], $app['java_path']));

        return $fm;
    })

);

$app['assetic.asset_manager'] = $app->share(

    $app->extend('assetic.asset_manager', function($am, $app) {
        
        $am->set('css', new Assetic\Asset\AssetCache(
            new Assetic\Asset\GlobAsset(
                // Specified one by one to define order
                array(
                    __DIR__ . '/../public/css/bootstrap.css',
                    __DIR__ . '/../public/css/datepicker.css',
                    __DIR__ . '/../public/font-awesome/css/font-awesome.css',
                    __DIR__ . '/../public/css/main.css'
                ),
                array($app['assetic.filter_manager']->get('css_filter'))
            ),
            new Assetic\Cache\FilesystemCache(__DIR__ . '/../cache/assetic')
        ));
        $am->get('css')->setTargetPath('css/concat.css');

        $am->set('js', new Assetic\Asset\AssetCache(
            new Assetic\Asset\GlobAsset(
                // Specified one by one to define order
                array(
                    __DIR__ . '/../public/js/jquery.js',
                    __DIR__ . '/../public/js/handlebars.js',
                    __DIR__ . '/../public/js/ember.js',
                    __DIR__ . '/../public/js/bootstrap.js',
                    __DIR__ . '/../public/js/twig.js',
                    __DIR__ . '/../public/js/timeago.js',
                    __DIR__ . '/../public/js/color.js',
                    __DIR__ . '/../public/js/datepicker.js',
                    __DIR__ . '/../public/js/asf.js',
                    __DIR__ . '/../public/js/dom.js'
                ),
                array($app['assetic.filter_manager']->get('js_filter'))
            ),
            new Assetic\Cache\FilesystemCache(__DIR__ . '/../cache/assetic')
        ));
        $am->get('js')->setTargetPath('js/concat.js');

        return $am;
    })

);

$truncate = new Twig_SimpleFunction('truncate', array('ASF\Utils', 'truncate'));
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
    return ASF\Permissions::hasPermission($action);
});

$repeat_function = new Twig_SimpleFunction('repeat', function ($string, $length) {
    return str_repeat($string, $length);
});

$to_date = new Twig_SimpleFilter('toDate', function ($string) use ($app) {
    $user = $app['session']->get('user');
    
    if (!$user)
    {
        // @todo add an admin setting for default date format
        return date('jS m y, H:i', $string);
    }
    else
    {
        if (!$user['settings']['date_format'])
        {
            return date('jS m y, H:i', $string);
        }

        return date($user['settings']['date_format'], $string);
    }
});

$app['twig']->addFunction($truncate);
$app['twig']->addFunction($config_function);
$app['twig']->addFunction($permissions_function);
$app['twig']->addFunction($repeat_function);
$app['twig']->addFilter($to_date);

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'dbname'    => $app['database']['name'],
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

ASF\Route::$app = $app;
ASF\Permissions::$app = $app;

// Models
include 'models.php';

// Routes
include 'routes.php';

if ($app['debug'] === true) 
{
    $app->register(new Whoops\Provider\Silex\WhoopsServiceProvider);

    if (strpos($_SERVER['REQUEST_URI'], '?purge') !== false)
    {
        $app['cache']->flush($app, $app['database']['name'], $app['database']['name']);
        
        $app['assetic.dumper']->dumpAssets();
    }
}