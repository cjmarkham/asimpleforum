<?php

use ASF\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/' . $app['board']['base'], function (ASFApplication $app) {
    return Route::get('home:index');
});

$app->get('/' . $app['board']['base'] . 'test/', function (ASFApplication $app) {
    return include 'test.php';
});

$app->get('/' . $app['board']['base'] . 'new-topics/', function (ASFApplication $app) {
   return Route::get('topic:newest');
});

$app->get('/' . $app['board']['base'] . 'signup/', function (ASFApplication $app) {
    return Route::get('auth:signup');
});

$app->get('/' . $app['board']['base'] . 'members/', function (ASFApplication $app) {
    return Route::get('members:index');
});

$app->post('/' . $app['board']['base'] . 'members/findAll/', function (Request $request) use ($app) {
    return $app['user']->findAll($request);
});

$app->get('/' . $app['board']['base'] . 'faqs/', function (ASFApplication $app) {
    return Route::get('faq:index');
});

$app->post('/' . $app['board']['base'] . 'signup/', function (Request $request) {
    return Route::get('auth:signup', $request);
});

$app->get('/' . $app['board']['base'] . 'search/', function (ASFApplication $app) {
    return Route::get('search:index');
});

$app->get('/' . $app['board']['base'] . 'search/typeahead/{query}/{selection}/', function (ASFApplication $app, $query, $selection) {
    return Route::get('search:typeahead', $query, $selection);
});

$app->get('/' . $app['board']['base'] . 'search/{query}/{selection}/', function (ASFApplication $app, $query, $selection) {
    return Route::get('search:get', $query, $selection);
});

$app->post('/' . $app['board']['base'] . 'login/', function (Request $request) use ($app) {
    return $app['auth']->login($request->request->all());
});

$app->get('/' . $app['board']['base'] . 'logout/', function (ASFApplication $app) {
    return Route::get('auth:logout');
});

$app->get('/' . $app['board']['base'] . 'download/', function (ASFApplication $app) {
    return Route::get('download:index');
});

$app->get('/' . $app['board']['base'] . 'user/{username}/{page}/', function (ASFApplication $app, $username, $page) {
    return Route::get('user:index', $username, $page);
})->assert('page', '([0-9]+)');

$app->get('/' . $app['board']['base'] . 'user/settings/', function (ASFApplication $app) {
    return Route::get('user:settings');
})->bind('user-settings');

$app->get('/' . $app['board']['base'] . 'user/settings/{section}', function (ASFApplication $app) {
    return Route::get('user:settings');
});

$app->get('/' . $app['board']['base'] . 'user/notifications/', function (ASFApplication $app) {
    return Route::get('user:notifications');
})->bind('notifications');

$app->get('/' . $app['board']['base'] . 'user/{username}/', function (ASFApplication $app, $username) {
    return Route::get('user:index', $username);
})->bind('user-profile');

$app->get('/' . $app['board']['base'] . 'user/confirmEmail/{code}/', function (ASFApplication $app, $code) {
    return Route::get('user:confirmEmail', $code);
});

$app->post('/' . $app['board']['base'] . 'partial/{name}/', function (Request $request, $name) use ($app) {

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

$app->post('/' . $app['board']['base'] . 'partial/{directory}/{name}/', function (Request $request, $directory, $name) use ($app) {

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

    return $app['twig']->render('Partials/' . $directory . '/' . $name . '.twig', $array);
});

$app->get('/' . $app['board']['base'] . 'partial/{name}/', function (ASFApplication $app, $name) {
    return $app['twig']->render('Partials/' . $name . '.twig', array(
        'user' => $app['session']->get('user')
    ));
});

$app->post('/' . $app['board']['base'] . 'sidebar/{name}/', function (Request $request, $name) use ($app) {

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

    return $app['twig']->render('Sidebars/' . $name . '.twig', $array);
});

$app->get('/' . $app['board']['base'] . 'sidebar/{name}/', function (ASFApplication $app, $name) {
    return $app['twig']->render('Sidebars/' . $name . '.twig', array(
        'user' => $app['session']->get('user')
    ));
});

$app->get('/' . $app['board']['base'] . '{name}-{id}/{page}/', function (ASFApplication $app, $name, $id, $page) {
    return Route::get('forum:index', $name, $id, $page);
})->assert('page', '([0-9]+)');

$app->get('/' . $app['board']['base'] . '{name}-{id}/', function (ASFApplication $app, $name, $id) {
    return Route::get('forum:index', $name, $id);
});

$app->get('/' . $app['board']['base'] . '{forum_name}/{topic_name}-{topic_id}/{page}/', function (ASFApplication $app, $topic_name, $topic_id, $page) {
    return Route::get('topic:index', $topic_name, $topic_id, $page);
})->assert('page', '([0-9]+)');

$app->get('/' . $app['board']['base'] . '{forum_name}/{topic_name}-{topic_id}/', function (ASFApplication $app, $topic_name, $topic_id) {
    return Route::get('topic:index', $topic_name, $topic_id);
});

$app->post('/' . $app['board']['base'] . 'forum/{method}/', function (Request $request, $method) use ($app) {
    // TODO: Check for allowed post methods
    if (!method_exists($app['forum'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['forum']->$method($request);
});

$app->post('/' . $app['board']['base'] . 'topic/{method}/', function (Request $request, $method) use ($app) {
    // @todo Check for allowed post methods
    if (!method_exists($app['topic'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['topic']->$method($request);
});

$app->post('/' . $app['board']['base'] . 'post/{method}/', function (Request $request, $method) use ($app) {
    // @todo Check for allowed post methods
    if (!method_exists($app['post'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    return $app['post']->$method($request);
});

$app->post('/' . $app['board']['base'] . 'user/save/{method}/', function (Request $request, $method) use ($app) {
    // @todo Check for allowed post methods
    $method = 'save' . ucfirst($method);

    if (!method_exists($app['user'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    
    return $app['user']->$method($request);
});

$app->post('/' . $app['board']['base'] . 'user/{method}/', function (Request $request, $method) use ($app) {
    // @todo Check for allowed post methods
    if (!method_exists($app['user'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    
    return $app['user']->$method($request);
});

$app->post('/' . $app['board']['base'] . 'notifications/{method}/', function (Request $request, $method) use ($app) {
    // @todo Check for allowed post methods
    if (!method_exists($app['notification'], $method))
    {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }
    
    return $app['notification']->$method($request);
});

// ADMIN ROUTES

$app->get('/' . $app['board']['base'] . 'admin/', function (ASFApplication $app) {
    return Route::get('admin/home:index');
});

$app->get('/' . $app['board']['base'] . 'admin/forums/', function (ASFApplication $app) {
    return Route::get('admin/forums:index');
});