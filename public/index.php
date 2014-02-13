<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require '../src/bootstrap.php';

$app->before(function (Request $request) use ($app) {

    // Get the logged in user
    $user = $app['session']->get('user');

    if ($user)
    {
        unset ($user['password']);
        $app['translator']->setLocale($user['locale']);
    }

    // Update the sessions
    $app['sessions']->update();
    $sessions = $app['sessions']->get();

    // Get the recent topics
    $recent_topics = $app['topic']->findRecent(4);

    // Used for twig globals
    $config = array(
        'default' => $app['defaults'],          
        'board' => $app['board'],
        'files' => $app['files']            
    );

    $avatar_root = '/' . $app['board']['base'] . '/uploads/avatars';

    // Assign some global template variables
    $app['twig']->addGlobal('user', $user);
    $app['twig']->addGlobal('config', $config);
    $app['twig']->addGlobal('recent_topics', $recent_topics);
    $app['twig']->addGlobal('sessions', $sessions);

    $app['twig']->addGlobal('avatar_root', $avatar_root);

});

$app->finish(function (Request $request) use ($app, $logger) {
    
    // Only run if not ajax request
    if (!$request->isXMLHttpRequest())
    {
        $time = 0;

        // Counts all of the queries for debugging
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