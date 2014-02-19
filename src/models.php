<?php

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
    $model = new ASF\Language($app);
    return $model;
});

$app['notification'] = $app->share(function() use ($app) {
    $model = new \Model\NotificationModel($app);
    return $model;
});