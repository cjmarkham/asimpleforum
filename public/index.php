<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require '../src/bootstrap.php';

$app->before(function (Request $request) use ($app) {
    $user = $app['session']->get('user');

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