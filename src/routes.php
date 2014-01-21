<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/', function (Application $app) {
    return Route::get('home:index');
});

$app->get('/test', function (Application $app) {
    return include 'test.php';
});

$app->get('/new-topics', function (Application $app) {
   return Route::get('topic:newest');
});

$app->get('/signup', function (Application $app) {
    return Route::get('auth:signup');
});

$app->get('/members', function (Application $app) {
    return Route::get('members:index');
});

$app->get('/faqs', function (Application $app) {
    return Route::get('faq:index');
});

$app->post('/signup', function (Request $request) {
    return Route::get('auth:signup', $request);
});

$app->get('/search', function (Application $app) {
    return Route::get('search:index');
});

$app->get('/search', function (Application $app) {
    return Route::get('search:index');
});

$app->get('/search/{query}/{selection}', function (Application $app, $query, $selection) {
    return Route::get('search:get', $query, $selection);
});

$app->post('/search/typeahead', function (Request $request) {
    return Route::get('search:typeahead', $request);
});

$app->post('/login', function (Request $request) use ($app) {
    return $app['auth']->login($request);
});

$app->get('/logout', function (Application $app) {
    return Route::get('auth:logout');
});

$app->get('/download', function (Application $app) {
    return Route::get('download:index');
});

$app->get('/user/{username}/{page}', function (Application $app, $username, $page) {
    return Route::get('user:index', $username, $page);
})->assert('page', '([0-9]+)');

$app->get('/user/{username}', function (Application $app, $username) {
    return Route::get('user:index', $username);
});

$app->get('/user/confirmEmail/{code}', function (Application $app, $code) {
    return Route::get('user:confirmEmail', $code);
});

$app->post('/partial/{name}', function (Request $request, $name) use ($app) {

    $params = $request->get('params');
    $array = array();

    if (isset($params) && is_array($params))
    {
        foreach ($params as $key => $param)
        {
            $array[$key] = $param;
        
        }
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
})->assert('topic_name', '([a-zA-Z0-9<>_-\s]+)');

$app->post('/forum/{method}', function (Request $request, $method) use ($app) {
    // TODO: Check for allowed post methods
    if (!method_exists($app['forum'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['forum']->$method($request);
});

$app->post('/topic/{method}', function (Request $request, $method) use ($app) {
    // TODO: Check for allowed post methods
    if (!method_exists($app['topic'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['topic']->$method($request);
});

$app->post('/post/{method}', function (Request $request, $method) use ($app) {
    // TODO: Check for allowed post methods
    if (!method_exists($app['post'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['post']->$method($request);
});

$app->post('/user/{method}', function (Request $request, $method) use ($app) {
    // TODO: Check for allowed post methods
    if (!method_exists($app['user'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['user']->$method($request);
});

// ADMIN ROUTES

$app->get('/admin', function (Application $app) {
    return Route::get('admin/home:index');
});

$app->get('/admin/forums', function (Application $app) {
    return Route::get('admin/forums:index');
});